<?php

declare(strict_types=1);

namespace App\Enums\SocialAccount;

use App\Enums\Media\Type as MediaType;

enum Platform: string
{
    case X = 'x';
    case TikTok = 'tiktok';
    case YouTube = 'youtube';
    case YouTubeLong = 'youtube-long';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case InstagramFacebook = 'instagram-facebook';
    case Threads = 'threads';

    public function label(): string
    {
        return match ($this) {
            self::X => 'X',
            self::TikTok => 'TikTok',
            self::YouTube => 'YouTube Shorts',
            self::YouTubeLong => 'YouTube (Videos)',
            self::Facebook => 'Facebook Page',
            self::Instagram => 'Instagram (Standalone)',
            self::InstagramFacebook => 'Instagram (Facebook Business)',
            self::Threads => 'Threads',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::X => '#000000',
            self::TikTok => '#000000',
            self::YouTube, self::YouTubeLong => '#FF0000',
            self::Facebook => '#1877F2',
            self::Instagram => '#E4405F',
            self::InstagramFacebook => '#E4405F',
            self::Threads => '#000000',
        };
    }

    public function allowedMediaTypes(): array
    {
        return match ($this) {
            self::X => [MediaType::Image, MediaType::Video],
            self::TikTok => [MediaType::Video],
            self::YouTube, self::YouTubeLong => [MediaType::Video],
            self::Facebook => [MediaType::Image, MediaType::Video],
            self::Instagram, self::InstagramFacebook => [MediaType::Image, MediaType::Video],
            self::Threads => [MediaType::Image, MediaType::Video],
        };
    }

    public function maxImages(): int
    {
        return match ($this) {
            self::X => 4,
            self::TikTok => 0,
            self::YouTube, self::YouTubeLong => 0,
            self::Facebook => 10,
            self::Instagram, self::InstagramFacebook => 10,
            self::Threads => 10,
        };
    }

    /**
     * Hard cap (in characters) the platform's API will accept. Going over this
     * means the post can't be published. Values are the documented API maxes:
     *
     *  - X standard tweet: 280 (X Premium accepts 25K — ignored, conservative)
     *  - TikTok caption: 2200
     *  - YouTube Shorts: title=100, description=5000. We feed `content` to both
     *    (publisher derives title from the first line via `buildTitle`), and
     *    Shorts UX only shows ~100 chars before "more" — capping at 100 keeps
     *    posts appropriate for the format.
     *  - YouTube Videos: title=100, description=5000
     *  - Facebook text status: 10000 (API allows 63206; we cap below
     *    that — 63k-char posts are unrealistic and emoji-heavy content
     *    risks overflowing the TEXT column's 65535-byte ceiling)
     *  - Instagram feed caption: 2200
     *  - Threads: 500
     */
    public function maxContentLength(): int
    {
        return match ($this) {
            self::X => 280,
            self::TikTok => 2200,
            self::YouTube, self::YouTubeLong => 5000,
            self::Facebook => 10000,
            self::Instagram, self::InstagramFacebook => 2200,
            self::Threads => 500,
        };
    }

    /**
     * Number of characters by which the given content exceeds this platform's
     * hard cap. Returns 0 when it fits. Single source of truth for content-
     * length checks — used both at schedule-validation time and at publish
     * time itself so the two paths can never drift apart.
     */
    public function contentOverflow(string $content): int
    {
        return max(0, mb_strlen($content) - $this->maxContentLength());
    }

    /**
     * Recommended target length (in characters) for AI-generated posts. This
     * is the engagement sweet spot — much shorter than the platform's hard
     * `maxContentLength()`. Use this to instruct the LLM at generation time;
     * use `maxContentLength()` for publish-time validation.
     */
    public function recommendedAiContentLength(): int
    {
        return match ($this) {
            // Microblogging — 70-200 char tweets perform best, leave hashtag room
            self::X => 220,
            // Threads — similar feel, slightly more relaxed
            self::Threads => 300,
            // Instagram captions — most viewers expand only when interested,
            // 100-150 words performs best
            self::Instagram, self::InstagramFacebook => 600,
            // Facebook — short posts dominate the algorithm
            self::Facebook => 280,
            // TikTok caption — the video carries the story
            self::TikTok => 150,
            // YouTube Shorts — fits within the 100-char title (with " #Shorts"
            // suffix taking 8 chars) so the same string works as title + desc
            self::YouTube => 80,
            // YouTube Videos — longer descriptions perform better
            self::YouTubeLong => 1500,
        };
    }

    /**
     * @return array<string>
     */
    public function requiredPublishScopes(): array
    {
        return match ($this) {
            self::Instagram => ['instagram_business_content_publish'],
            self::InstagramFacebook => ['instagram_content_publish'],
            self::Facebook => ['pages_manage_posts'],
            self::TikTok => ['video.publish'],
            self::YouTube, self::YouTubeLong => ['https://www.googleapis.com/auth/youtube.upload'],
            self::X => ['tweet.write'],
            self::Threads => ['threads_content_publish'],
        };
    }

    public function supportsTextOnly(): bool
    {
        return match ($this) {
            self::X => true,
            self::TikTok => false,
            self::YouTube, self::YouTubeLong => false,
            self::Facebook => true,
            self::Instagram, self::InstagramFacebook => false,
            self::Threads => true,
        };
    }

    public function requiresContent(): bool
    {
        return match ($this) {
            self::YouTube => true,
            default => false,
        };
    }

    public function queue(): string
    {
        return 'social-'.$this->value;
    }

    /**
     * @return array<string>
     */
    public static function allQueues(): array
    {
        return array_map(fn (self $platform) => $platform->queue(), self::cases());
    }

    public function instagramGraphBaseUrl(): string
    {
        return match ($this) {
            self::InstagramFacebook => (string) config('trypost.platforms.instagram-facebook.graph_api'),
            default => (string) config('trypost.platforms.instagram.graph_api'),
        };
    }

    public function isEnabled(): bool
    {
        return config("trypost.platforms.{$this->value}.enabled", true);
    }

    /**
     * Static, platform-specific data exposed to the frontend (e.g. TikTok privacy options,
     * compliance URLs). Returns an empty array for platforms with no extra config.
     *
     * @return array<string, mixed>
     */
    public function publishConfig(): array
    {
        return match ($this) {
            self::TikTok => [
                'privacyLevelOptions' => [
                    'PUBLIC_TO_EVERYONE',
                    'MUTUAL_FOLLOW_FRIENDS',
                    'FOLLOWER_OF_CREATOR',
                    'SELF_ONLY',
                ],
                'musicUsageConfirmationUrl' => 'https://www.tiktok.com/legal/page/global/music-usage-confirmation/en',
                'brandedContentPolicyUrl' => 'https://www.tiktok.com/legal/page/global/bc-policy/en',
            ],
            default => [],
        };
    }

    /**
     * Get all enabled platforms.
     *
     * @return array<self>
     */
    public static function enabled(): array
    {
        return array_filter(self::cases(), fn (self $platform) => $platform->isEnabled());
    }
}
