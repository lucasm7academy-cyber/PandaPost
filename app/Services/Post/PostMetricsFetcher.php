<?php

declare(strict_types=1);

namespace App\Services\Post;

use App\Enums\SocialAccount\Platform;
use App\Models\Post;
use App\Models\PostPlatform;
use App\Services\Social\FacebookAnalytics;
use App\Services\Social\InstagramAnalytics;
use App\Services\Social\ThreadsAnalytics;
use App\Services\Social\XAnalytics;
use App\Services\Social\YouTubeAnalytics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Fetches per-platform post metrics. Used by the web controller, the REST
 * API controller, and the MCP `GetPostMetricsTool` so that the per-platform
 * dispatch and 5-minute cache stay in one place.
 */
class PostMetricsFetcher
{
    /**
     * @return Collection<int, array{
     *     post_platform_id: string,
     *     platform: string,
     *     status: string,
     *     platform_post_id: ?string,
     *     platform_url: ?string,
     *     metrics: array<int, array{label: string, value: int}>|array{unsupported: true, reason: string}
     * }>
     */
    public function forPost(Post $post): Collection
    {
        return $post->postPlatforms
            ->where('enabled', true)
            ->values()
            ->map(fn (PostPlatform $pp) => [
                'post_platform_id' => $pp->id,
                'platform' => $pp->platform->value,
                'status' => $pp->status->value,
                'platform_post_id' => $pp->platform_post_id,
                'platform_url' => $pp->platform_url,
                'metrics' => $this->forPlatform($pp),
            ]);
    }

    /**
     * @return array<int, array{label: string, value: int}>|array{unsupported: true, reason: string}
     */
    public function forPlatform(PostPlatform $postPlatform): array
    {
        if ($postPlatform->status->value !== 'published' || ! $postPlatform->platform_post_id) {
            return ['unsupported' => true, 'reason' => 'not_published'];
        }

        return Cache::remember("post_metrics:{$postPlatform->id}", 300, fn () => match ($postPlatform->platform) {
            Platform::X => app(XAnalytics::class)->fetchPostMetrics($postPlatform),
            Platform::Instagram, Platform::InstagramFacebook => app(InstagramAnalytics::class)->fetchPostMetrics($postPlatform),
            Platform::Facebook => app(FacebookAnalytics::class)->fetchPostMetrics($postPlatform),
            Platform::Threads => app(ThreadsAnalytics::class)->fetchPostMetrics($postPlatform),
            Platform::YouTube, Platform::YouTubeLong => app(YouTubeAnalytics::class)->fetchPostMetrics($postPlatform),
            default => ['unsupported' => true, 'reason' => 'platform_not_supported'],
        });
    }
}
