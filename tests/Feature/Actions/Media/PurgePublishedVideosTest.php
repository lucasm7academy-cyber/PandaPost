<?php

declare(strict_types=1);

use App\Actions\Media\PurgePublishedVideos;
use App\Enums\Post\Status;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();

    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['user_id' => $this->user->id]);

    config()->set('trypost.media.purge.published_after_days', 2);
    config()->set('trypost.media.purge.orphan_after_days', 30);
});

/**
 * Create a video asset in the workspace library with a real file on the fake disk.
 */
function videoAsset(Workspace $workspace, ?string $createdAt = null): Media
{
    $media = Media::factory()->video()->create([
        'mediable_type' => $workspace->getMorphClass(),
        'mediable_id' => $workspace->id,
        'collection' => 'assets',
        'created_at' => $createdAt ?? now(),
    ]);

    Storage::put($media->path, 'fake-video-bytes');

    return $media;
}

/**
 * Attach a media item to a post's JSON `media` snapshot, the way the editor does.
 */
function postUsing(Workspace $workspace, Media $media, Status $status, ?string $publishedAt = null): Post
{
    return Post::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $workspace->user_id,
        'status' => $status,
        'published_at' => $publishedAt,
        'media' => [[
            'id' => $media->id,
            'path' => $media->path,
            'url' => $media->url,
            'mime_type' => $media->mime_type,
            'original_filename' => $media->original_filename,
        ]],
    ]);
}

test('purges a video whose post was published beyond the grace period', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Published, now()->subDays(5)->toDateTimeString());

    $result = (new PurgePublishedVideos)->execute();

    expect(data_get($result, 'purged'))->toBe(1);
    Storage::assertMissing($media->path);
});

test('keeps the media row so the library still shows which file was used', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Published, now()->subDays(5)->toDateTimeString());

    (new PurgePublishedVideos)->execute();

    $media->refresh();
    expect($media->exists)->toBeTrue();
    expect($media->original_filename)->not->toBeNull();
    expect(data_get($media->meta, 'purged_at'))->not->toBeNull();
});

test('flags the post media snapshot so the post shows a placeholder', function () {
    $media = videoAsset($this->workspace);
    $post = postUsing($this->workspace, $media, Status::Published, now()->subDays(5)->toDateTimeString());

    (new PurgePublishedVideos)->execute();

    expect(data_get($post->refresh()->media, '0.purged'))->toBeTrue();
});

test('never purges a video still attached to a scheduled post', function () {
    $media = videoAsset($this->workspace, now()->subYear()->toDateTimeString());
    postUsing($this->workspace, $media, Status::Scheduled);

    $result = (new PurgePublishedVideos)->execute();

    expect(data_get($result, 'purged'))->toBe(0);
    Storage::assertExists($media->path);
});

test('never purges a video shared between a published post and a scheduled one', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Published, now()->subDays(10)->toDateTimeString());
    postUsing($this->workspace, $media, Status::Scheduled);

    (new PurgePublishedVideos)->execute();

    Storage::assertExists($media->path);
});

test('respects the grace period after publishing', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Published, now()->subHours(6)->toDateTimeString());

    (new PurgePublishedVideos)->execute();

    Storage::assertExists($media->path);
});

test('purges an orphan video older than the orphan window', function () {
    $media = videoAsset($this->workspace, now()->subDays(45)->toDateTimeString());

    (new PurgePublishedVideos)->execute();

    Storage::assertMissing($media->path);
});

test('keeps a recently uploaded video that is not attached to any post yet', function () {
    $media = videoAsset($this->workspace, now()->subDays(3)->toDateTimeString());

    (new PurgePublishedVideos)->execute();

    Storage::assertExists($media->path);
});

test('never touches images', function () {
    $image = Media::factory()->create([
        'mediable_type' => $this->workspace->getMorphClass(),
        'mediable_id' => $this->workspace->id,
        'collection' => 'assets',
        'created_at' => now()->subYear(),
    ]);
    Storage::put($image->path, 'fake-image-bytes');

    (new PurgePublishedVideos)->execute();

    Storage::assertExists($image->path);
});

test('dry run reports the reclaimable space without deleting anything', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Published, now()->subDays(5)->toDateTimeString());

    $result = (new PurgePublishedVideos)->execute(dryRun: true);

    expect(data_get($result, 'purged'))->toBe(1);
    expect(data_get($result, 'bytes_freed'))->toBe((int) $media->size);
    Storage::assertExists($media->path);
    expect(data_get($media->refresh()->meta, 'purged_at'))->toBeNull();
});

test('does not purge the same video twice', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Published, now()->subDays(5)->toDateTimeString());

    (new PurgePublishedVideos)->execute();
    $second = (new PurgePublishedVideos)->execute();

    expect(data_get($second, 'purged'))->toBe(0);
    expect(data_get($second, 'bytes_freed'))->toBe(0);
});

test('keeps a video whose post failed, since a retry re-uploads the file', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Failed, now()->subDays(10)->toDateTimeString());

    (new PurgePublishedVideos)->execute();

    Storage::assertExists($media->path);
});

test('keeps the file when another media row still points at the same path', function () {
    $media = videoAsset($this->workspace);
    postUsing($this->workspace, $media, Status::Published, now()->subDays(5)->toDateTimeString());

    // A second row sharing the file — e.g. the same upload reused elsewhere.
    Media::factory()->video()->create([
        'mediable_type' => $this->workspace->getMorphClass(),
        'mediable_id' => $this->workspace->id,
        'collection' => 'assets',
        'path' => $media->path,
        'meta' => ['purged_at' => now()->toIso8601String()],
    ]);

    (new PurgePublishedVideos)->execute();

    Storage::assertExists($media->path);
});
