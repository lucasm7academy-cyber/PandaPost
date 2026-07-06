<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Post\CreatePost;
use App\Enums\BulkSchedule\Status as BulkStatus;
use App\Enums\Post\Status as PostStatus;
use App\Events\BulkScheduleProgress;
use App\Models\BulkSchedule;
use App\Models\Media;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Ai\AiCaptionVariations;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessBulkScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $bulkScheduleId) {}

    public function handle(): void
    {
        $bulkSchedule = BulkSchedule::find($this->bulkScheduleId);

        if (! $bulkSchedule) {
            Log::warning('ProcessBulkScheduleJob: BulkSchedule not found', [
                'bulk_schedule_id' => $this->bulkScheduleId,
            ]);

            return;
        }

        $workspace = Workspace::find($bulkSchedule->workspace_id);
        $user = User::find($bulkSchedule->user_id);

        if (! $workspace || ! $user) {
            $bulkSchedule->markAsFailed(__('posts.bulk.errors.missing_context'));
            BulkScheduleProgress::dispatch($bulkSchedule->fresh());

            return;
        }

        $bulkSchedule->markAsProcessing();
        BulkScheduleProgress::dispatch($bulkSchedule->fresh());

        try {
            $slots = $this->buildSlots($bulkSchedule);
            $mediaIds = array_values($bulkSchedule->media_ids);
            $totalPosts = min(count($mediaIds), count($slots));

            if ($totalPosts === 0) {
                $bulkSchedule->markAsCompleted();
                BulkScheduleProgress::dispatch($bulkSchedule->fresh());

                return;
            }

            $captions = AiCaptionVariations::generate(
                workspace: $workspace,
                prompt: $bulkSchedule->prompt,
                count: $totalPosts,
                userId: $user->id,
            );

            $platforms = array_values($bulkSchedule->platforms);

            for ($i = 0; $i < $totalPosts; $i++) {
                $mediaId = data_get($mediaIds, $i);
                $media = Media::find($mediaId);

                if (! $media) {
                    // Skip missing media but keep slot bookkeeping consistent.
                    Log::warning('ProcessBulkScheduleJob: media not found, skipping', [
                        'bulk_schedule_id' => $bulkSchedule->id,
                        'media_id' => $mediaId,
                    ]);

                    continue;
                }

                $mediaPayload = [[
                    'id' => $media->id,
                    'path' => $media->path,
                    'url' => $media->url,
                    'type' => $media->type->value,
                    'mime_type' => $media->mime_type,
                ]];

                $content = data_get($captions, $i, $bulkSchedule->prompt);
                
                if ($bulkSchedule->signature_id && $bulkSchedule->signature) {
                    $content = trim($content) . "\n\n" . $bulkSchedule->signature->content;
                }

                $post = CreatePost::execute($workspace, $user, [
                    'content' => $content,
                    'media' => $mediaPayload,
                    'scheduled_at' => $slots[$i]->toIso8601String(),
                    'platforms' => $platforms,
                ]);

                $post->update(['status' => PostStatus::Scheduled]);

                $bulkSchedule->increment('created_posts');
                BulkScheduleProgress::dispatch($bulkSchedule->fresh());
            }

            $bulkSchedule->markAsCompleted();
            BulkScheduleProgress::dispatch($bulkSchedule->fresh());
        } catch (Throwable $e) {
            Log::error('ProcessBulkScheduleJob failed', [
                'bulk_schedule_id' => $bulkSchedule->id,
                'error' => $e->getMessage(),
            ]);

            $bulkSchedule->markAsFailed($e->getMessage());
            BulkScheduleProgress::dispatch($bulkSchedule->fresh());

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $bulkSchedule = BulkSchedule::find($this->bulkScheduleId);

        if (! $bulkSchedule || $bulkSchedule->status === BulkStatus::Completed) {
            return;
        }

        $bulkSchedule->markAsFailed($exception?->getMessage());
        BulkScheduleProgress::dispatch($bulkSchedule->fresh());
    }

    /**
     * Build the ordered list of UTC datetimes (one per day×time combination).
     *
     * @return array<int, Carbon>
     */
    private function buildSlots(BulkSchedule $bulkSchedule): array
    {
        $days = $bulkSchedule->days;
        $times = $bulkSchedule->times;
        sort($days);
        sort($times);

        $tz = $bulkSchedule->timezone ?: 'UTC';
        $slots = [];

        foreach ($days as $day) {
            foreach ($times as $time) {
                $slots[] = Carbon::parse("{$day} {$time}:00", $tz)->utc();
            }
        }

        return $slots;
    }
}
