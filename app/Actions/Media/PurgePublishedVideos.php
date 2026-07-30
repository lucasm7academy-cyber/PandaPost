<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Enums\Post\Status;
use App\Models\Media;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes video files whose posts have all been published, reclaiming disk
 * space on the server.
 *
 * The `medias` row and every post's media entry survive the purge — they are
 * flagged instead of removed, so the asset library and the post history still
 * show which video was used. Only the bytes on disk are freed.
 *
 * A video is never purged while any post referencing it is still unpublished.
 */
class PurgePublishedVideos
{
    /**
     * Statuses that mean a post may still need its video file.
     *
     * `Failed` is included on purpose: a failed post can be retried, and the
     * retry re-uploads the original file to the platform.
     */
    private const array PENDING_STATUSES = [
        Status::Draft,
        Status::Scheduled,
        Status::Publishing,
        Status::Failed,
        Status::PartiallyPublished,
    ];

    /**
     * @return array{purged: int, bytes_freed: int, skipped: int}
     */
    public function execute(bool $dryRun = false): array
    {
        $publishedCutoff = CarbonImmutable::now()
            ->subDays((int) config('trypost.media.purge.published_after_days'));

        $orphanCutoff = CarbonImmutable::now()
            ->subDays((int) config('trypost.media.purge.orphan_after_days'));

        $purged = 0;
        $bytesFreed = 0;
        $skipped = 0;

        // Several media rows can point at the same file on disk (Media::delete
        // guards against this too). Decide per file, not per row, so one row
        // being eligible can never delete a file another row still needs.
        $this->purgeableCandidates()
            ->groupBy('path')
            ->each(function (Collection $group, string $path) use (
                $publishedCutoff, $orphanCutoff, $dryRun, &$purged, &$bytesFreed, &$skipped
            ) {
                if ($this->hasUnpurgedSiblingOutsideGroup($path, $group)) {
                    $skipped += $group->count();

                    return;
                }

                $posts = $this->postsReferencing($path);

                $allEligible = $group->every(
                    fn (Media $media) => $this->isEligible($posts, $media, $publishedCutoff, $orphanCutoff)
                );

                if (! $allEligible) {
                    $skipped += $group->count();

                    return;
                }

                $bytesFreed += (int) $group->first()->size;
                $purged += $group->count();

                if (! $dryRun) {
                    $this->purgeGroup($group, $path, $posts);
                }
            });

        return ['purged' => $purged, 'bytes_freed' => $bytesFreed, 'skipped' => $skipped];
    }

    /**
     * True when a media row outside this candidate set still points at the same
     * file — e.g. an image row, or a video already flagged. Those rows expect
     * the file to exist, so it must stay.
     *
     * @param  Collection<int, Media>  $group
     */
    private function hasUnpurgedSiblingOutsideGroup(string $path, Collection $group): bool
    {
        return Media::query()
            ->where('path', $path)
            ->whereNotIn('id', $group->pluck('id'))
            ->exists();
    }

    /**
     * Video media that still holds a file on disk.
     *
     * `meta->purged_at` marks rows already processed, so repeated runs stay
     * cheap and never re-count freed bytes.
     *
     * @return Collection<int, Media>
     */
    private function purgeableCandidates(): Collection
    {
        return Media::query()
            ->where('type', 'video')
            ->whereNull('meta->purged_at')
            ->get();
    }

    /**
     * Posts whose JSON `media` column contains an item with this path.
     *
     * The column is `json`, so Laravel's Postgres grammar casts it to `jsonb`
     * for the containment check.
     *
     * @return Collection<int, Post>
     */
    private function postsReferencing(string $path): Collection
    {
        return Post::query()
            ->whereJsonContains('media', [['path' => $path]])
            ->get();
    }

    /**
     * @param  Collection<int, Post>  $posts
     */
    private function isEligible(
        Collection $posts,
        Media $media,
        CarbonImmutable $publishedCutoff,
        CarbonImmutable $orphanCutoff,
    ): bool {
        if ($posts->isEmpty()) {
            // Never attached to a post: fall back to the (longer) orphan window.
            return $media->created_at->lessThanOrEqualTo($orphanCutoff);
        }

        // Any post still waiting to go out keeps the file alive, regardless of age.
        $hasPending = $posts->contains(
            fn (Post $post) => in_array($post->status, self::PENDING_STATUSES, true)
        );

        if ($hasPending) {
            return false;
        }

        // Every referencing post is published — the most recent one sets the clock.
        $latestPublishedAt = $posts
            ->map(fn (Post $post) => $post->published_at)
            ->filter()
            ->max();

        if (! $latestPublishedAt) {
            return false;
        }

        return $latestPublishedAt->lessThanOrEqualTo($publishedCutoff);
    }

    /**
     * Flag every reference to the file, then delete it. The flagging runs in a
     * transaction so a mid-purge failure cannot leave posts pointing at a file
     * that is gone; the unlink happens after the commit, because a rolled-back
     * transaction cannot restore bytes.
     *
     * @param  Collection<int, Media>  $group
     * @param  Collection<int, Post>  $posts
     */
    private function purgeGroup(Collection $group, string $path, Collection $posts): void
    {
        $purgedAt = CarbonImmutable::now()->toIso8601String();

        DB::transaction(function () use ($group, $path, $posts, $purgedAt) {
            $group->each(fn (Media $media) => $media->update([
                'meta' => array_merge($media->meta ?? [], [
                    'purged_at' => $purgedAt,
                    'purged_bytes' => (int) $media->size,
                ]),
            ]));

            $posts->each(fn (Post $post) => $this->flagPostMedia($post, $path, $purgedAt));
        });

        Storage::delete($path);

        Log::info('Purged published video to reclaim disk space', [
            'media_ids' => $group->pluck('id')->all(),
            'path' => $path,
            'bytes' => (int) $group->first()->size,
            'posts' => $posts->pluck('id')->all(),
        ]);
    }

    /**
     * Mark the matching entries in a post's JSON `media` snapshot as purged.
     *
     * The snapshot is self-contained by design (every read path renders it
     * without touching the medias table), so the flag has to be written into
     * it rather than resolved at render time.
     */
    private function flagPostMedia(Post $post, string $path, string $purgedAt): void
    {
        $items = collect($post->media ?? [])
            ->map(function (array $item) use ($path, $purgedAt) {
                if (data_get($item, 'path') !== $path) {
                    return $item;
                }

                return array_merge($item, [
                    'purged' => true,
                    'purged_at' => $purgedAt,
                ]);
            })
            ->all();

        $post->forceFill(['media' => $items])->saveQuietly();
    }
}
