<?php

namespace Botble\Ads\Models;

use Botble\Ads\Database\Factories\AdsFactory;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Media\Facades\RvMedia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ads extends BaseModel
{
    use HasFactory;
    protected $table = 'ads';

    protected $fillable = [
        'name',
        'key',
        'status',
        'open_in_new_tab',
        'expired_at',
        'location',
        'image',
        'tablet_image',
        'mobile_image',
        'url',
        'clicked',
        'order',
        'ads_type',
        'google_adsense_slot_id',
        'ad_media_type',
        'video_url',
        'video_thumbnail',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'expired_at' => 'date',
        'open_in_new_tab' => 'boolean',
    ];

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->whereDate('expired_at', '>=', Carbon::now());
    }

    protected function randomHash(): Attribute
    {
        return Attribute::get(fn () => hash('sha1', $this->key . $this->getKey()));
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            function (): ?string {
                if (config('plugins.ads.general.use_real_image_url')) {
                    return RvMedia::getImageUrl($this->image);
                }

                return $this->parseImageUrl();
            }
        );
    }

    protected function tabletImageUrl(): Attribute
    {
        return Attribute::get(
            function (): ?string {
                if (config('plugins.ads.general.use_real_image_url')) {
                    return RvMedia::getImageUrl($this->tablet_image ?: $this->image);
                }

                return $this->parseImageUrl('tablet');
            }
        );
    }

    protected function mobileImageUrl(): Attribute
    {
        return Attribute::get(
            function (): ?string {
                if (config('plugins.ads.general.use_real_image_url')) {
                    return RvMedia::getImageUrl(($this->mobile_image ?: $this->tablet_image) ?: $this->image);
                }

                return $this->parseImageUrl('mobile');
            }
        );
    }

    protected function clickUrl(): Attribute
    {
        return Attribute::get(
            fn ($_, array $attributes = []): string =>
                route('public.ads-click.alternative', [
                    'randomHash' => $this->random_hash,
                    'adsKey' => $attributes['key'],
                ])
        );
    }

    public function parseImageUrl(string $size = 'default'): string
    {
        return route('public.ads-click.image', [
            'randomHash' => $this->random_hash,
            'adsKey' => $this->key,
            'size' => $size,
            'hashName' => md5($this->key),
        ]);
    }

    protected function videoThumbnailUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->video_thumbnail ? RvMedia::getImageUrl($this->video_thumbnail) : null
        );
    }

    public function isVideoAd(): bool
    {
        return $this->ad_media_type === 'video' && ! empty($this->video_url);
    }

    public function getEmbedVideoUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $url = $this->video_url;

        // Convert YouTube URLs to embed format
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1';
        }

        // Convert Vimeo URLs to embed format
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1] . '?autoplay=1';
        }

        // Return as-is for direct video URLs
        return $url;
    }

    protected static function newFactory()
    {
        return AdsFactory::new();
    }
}
