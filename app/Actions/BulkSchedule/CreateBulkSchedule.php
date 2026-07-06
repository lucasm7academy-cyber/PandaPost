<?php

declare(strict_types=1);

namespace App\Actions\BulkSchedule;

use App\Enums\BulkSchedule\Status;
use App\Models\BulkSchedule;
use App\Models\User;
use App\Models\Workspace;

class CreateBulkSchedule
{
    /**
     * Persist a BulkSchedule row in `pending` state. Total post count is
     * derived from min(media_ids, days × times) — slots without a matching
     * media are silently discarded.
     *
     * @param  array{
     *     media_ids: array<int, string>,
     *     platforms: array<int, array{social_account_id: string, content_type?: string}>,
     *     days: array<int, string>,
     *     times: array<int, string>,
     *     timezone: string,
     *     prompt: string,
     *     signature_id?: string|null
     * }  $data
     */
    public static function execute(Workspace $workspace, User $user, array $data): BulkSchedule
    {
        $mediaIds = array_values(data_get($data, 'media_ids', []));
        $platforms = array_values(data_get($data, 'platforms', []));
        $days = array_values(data_get($data, 'days', []));
        $times = array_values(data_get($data, 'times', []));

        sort($days);
        sort($times);

        $totalSlots = count($days) * count($times);
        $totalPosts = min(count($mediaIds), $totalSlots);

        return BulkSchedule::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'media_ids' => $mediaIds,
            'platforms' => $platforms,
            'days' => $days,
            'times' => $times,
            'timezone' => (string) data_get($data, 'timezone', 'UTC'),
            'prompt' => (string) data_get($data, 'prompt', ''),
            'signature_id' => data_get($data, 'signature_id'),
            'status' => Status::Pending,
            'total_posts' => $totalPosts,
            'created_posts' => 0,
        ]);
    }
}
