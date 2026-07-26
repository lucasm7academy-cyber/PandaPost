<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Media\PurgePublishedVideos as PurgePublishedVideosAction;
use Illuminate\Console\Command;

class PurgePublishedVideos extends Command
{
    protected $signature = 'media:purge-published-videos {--dry-run : Report what would be freed without deleting anything}';

    protected $description = 'Delete video files whose posts have all been published, reclaiming disk space';

    public function handle(PurgePublishedVideosAction $action): int
    {
        if (! config('trypost.media.purge.enabled')) {
            $this->components->warn('Media purging is disabled (MEDIA_PURGE_ENABLED=false).');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $result = $action->execute($dryRun);

        $freed = number_format(data_get($result, 'bytes_freed') / 1024 / 1024, 1);
        $verb = $dryRun ? 'would be purged' : 'purged';

        $this->components->info(
            "{$result['purged']} video(s) {$verb}, {$freed} MB freed, {$result['skipped']} kept."
        );

        return self::SUCCESS;
    }
}
