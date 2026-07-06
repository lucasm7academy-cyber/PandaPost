<?php

declare(strict_types=1);

namespace App\Enums\PostPlatform;

use App\Enums\SocialAccount\Platform as SocialPlatform;

enum ContentType: string
{
    // Instagram
    case InstagramFeed = 'instagram_feed';
    case InstagramCarousel = 'instagram_carousel';
    case InstagramReel = 'instagram_reel';
    case InstagramStory = 'instagram_story';

    // Facebook
    case FacebookPost = 'facebook_post';
    case FacebookReel = 'facebook_reel';
    case FacebookStory = 'facebook_story';

    // TikTok
    case TikTokVideo = 'tiktok_video';
    case TikTokPhoto = 'tiktok_photo';

    // YouTube Shorts
    case YouTubeShort = 'youtube_short';

    // YouTube Videos (long-form)
    case YouTubeVideo = 'youtube_video';

    // X (Twitter)
    case XPost = 'x_post';

    // Threads
    case ThreadsPost = 'threads_post';

    public function label(): string
    {
        return match ($this) {
            self::InstagramFeed => 'Feed Post',
            self::InstagramCarousel => 'Carousel',
            self::InstagramReel => 'Reel',
            self::InstagramStory => 'Story',
            self::FacebookPost => 'Post',
            self::FacebookReel => 'Reel',
            self::FacebookStory => 'Story',
            self::TikTokVideo => 'Video',
            self::TikTokPhoto => 'Photo carousel',
            self::YouTubeShort => 'Short',
            self::YouTubeVideo => 'Video',
            self::XPost => 'Post',
            self::ThreadsPost => 'Post',
        };
    }

    public function description(): string
    {
        return (string) trans("posts.content_types.{$this->value}.description");
    }

    public function platform(): SocialPlatform
    {
        return match ($this) {
            self::InstagramFeed, self::InstagramCarousel, self::InstagramReel, self::InstagramStory => SocialPlatform::Instagram,
            self::FacebookPost, self::FacebookReel, self::FacebookStory => SocialPlatform::Facebook,
            self::TikTokVideo, self::TikTokPhoto => SocialPlatform::TikTok,
            self::YouTubeShort => SocialPlatform::YouTube,
            self::YouTubeVideo => SocialPlatform::YouTubeLong,
            self::XPost => SocialPlatform::X,
            self::ThreadsPost => SocialPlatform::Threads,
        };
    }

    /**
     * Image dimensions used by the AI generator for this format.
     * Single source of truth — `TemplateImageGenerator` reads from here.
     *
     * @return array{width: int, height: int}
     */
    public function aiImageDimensions(): array
    {
        return match ($this) {
            // Vertical 4:5 (Instagram preferred portrait, Threads mirrors it)
            self::InstagramFeed,
            self::InstagramCarousel,
            self::ThreadsPost => ['width' => 1080, 'height' => 1350],

            // Square 1:1 (X, Facebook)
            self::FacebookPost,
            self::XPost => ['width' => 1080, 'height' => 1080],

            // Stories 9:16 (Instagram + Facebook)
            self::InstagramStory,
            self::FacebookStory => ['width' => 1080, 'height' => 1920],

            // Default: 4:5 portrait
            default => ['width' => 1080, 'height' => 1350],
        };
    }

    public function aspectRatio(): ?string
    {
        return match ($this) {
            self::InstagramFeed, self::InstagramCarousel => '4:5',
            self::InstagramReel, self::InstagramStory => '9:16',
            self::FacebookReel, self::FacebookStory => '9:16',
            self::TikTokVideo, self::YouTubeShort, self::YouTubeVideo => '9:16',
            self::TikTokPhoto => '1:1',
            default => null,
        };
    }

    public function maxMediaCount(): int
    {
        return match ($this) {
            self::InstagramFeed => 1,
            self::InstagramCarousel => 10,
            self::InstagramReel, self::InstagramStory => 1,
            self::FacebookPost => 10,
            self::FacebookReel, self::FacebookStory => 1,
            self::TikTokVideo => 1,
            self::TikTokPhoto => 35,
            self::YouTubeShort, self::YouTubeVideo => 1,
            self::XPost => 4,
            self::ThreadsPost => 10,
        };
    }

    public function supportsVideo(): bool
    {
        return match ($this) {
            self::InstagramFeed, self::InstagramReel, self::InstagramStory => true,
            self::InstagramCarousel => false,
            self::FacebookPost, self::FacebookReel, self::FacebookStory => true,
            self::TikTokVideo => true,
            self::TikTokPhoto => false,
            self::YouTubeShort, self::YouTubeVideo => true,
            self::XPost => true,
            self::ThreadsPost => true,
        };
    }

    public function supportsImage(): bool
    {
        return match ($this) {
            self::InstagramReel => false,
            self::TikTokVideo => false,
            self::TikTokPhoto => true,
            self::YouTubeShort, self::YouTubeVideo => false,
            default => true,
        };
    }

    /**
     * Whether this content type carries a text caption visible to viewers.
     * Stories are image-overlay only — viewers don't see a separate caption.
     */
    public function supportsCaption(): bool
    {
        return match ($this) {
            self::InstagramStory, self::FacebookStory => false,
            default => true,
        };
    }

    public function requiresMedia(): bool
    {
        return match ($this) {
            self::XPost => false,
            self::ThreadsPost => false,
            self::FacebookPost => false,
            self::InstagramFeed => false,
            default => true,
        };
    }

    /**
     * Content types that the AI generator currently supports. Reels/stories/
     * videos are excluded because the AI flow only produces text + images.
     *
     * @return array<self>
     */
    public static function aiSupported(): array
    {
        return [
            self::InstagramFeed,
            self::InstagramCarousel,
            self::InstagramStory,
            self::XPost,
            self::ThreadsPost,
            self::FacebookPost,
            self::FacebookStory,
        ];
    }

    /**
     * Platforms this content_type can be assigned to. Most content types are
     * tied to a single platform; Instagram-family types (feed/carousel/reel/
     * story) work for both `Instagram` (Basic Display) and `InstagramFacebook`
     * (Business via Facebook Page) accounts.
     *
     * @return array<SocialPlatform>
     */
    public function compatiblePlatforms(): array
    {
        $primary = $this->platform();

        return match ($primary) {
            SocialPlatform::Instagram => [SocialPlatform::Instagram, SocialPlatform::InstagramFacebook],
            default => [$primary],
        };
    }

    /**
     * Get all content types for a specific platform. Treats InstagramFacebook
     * as an alias for Instagram so both flavors get the same content types.
     *
     * @return array<self>
     */
    public static function forPlatform(SocialPlatform $platform): array
    {
        $effective = match ($platform) {
            SocialPlatform::InstagramFacebook => SocialPlatform::Instagram,
            default => $platform,
        };

        return array_filter(
            self::cases(),
            fn (self $type) => $type->platform() === $effective
        );
    }

    /**
     * Get the default content type for a platform.
     */
    public static function defaultFor(SocialPlatform $platform): self
    {
        return match ($platform) {
            SocialPlatform::Instagram, SocialPlatform::InstagramFacebook => self::InstagramFeed,
            SocialPlatform::Facebook => self::FacebookPost,
            SocialPlatform::TikTok => self::TikTokVideo,
            SocialPlatform::YouTube => self::YouTubeShort,
            SocialPlatform::YouTubeLong => self::YouTubeVideo,
            SocialPlatform::X => self::XPost,
            SocialPlatform::Threads => self::ThreadsPost,
        };
    }
}
