<?php

declare(strict_types=1);

use App\Console\Commands\CheckSocialConnections;
use App\Console\Commands\ProcessScheduledPosts;
use App\Console\Commands\PurgePublishedVideos;
use App\Console\Commands\RecoverStuckPosts;
use App\Console\Commands\RefreshExpiringTokens;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ProcessScheduledPosts::class)->everyMinute()->withoutOverlapping()->onOneServer();
Schedule::command(CheckSocialConnections::class)->daily()->withoutOverlapping()->onOneServer();
Schedule::command(RefreshExpiringTokens::class)->hourly()->withoutOverlapping()->onOneServer();
Schedule::command(RecoverStuckPosts::class)->everyThirtyMinutes()->withoutOverlapping()->onOneServer();
Schedule::command(PurgePublishedVideos::class)->dailyAt('04:00')->withoutOverlapping()->onOneServer();
