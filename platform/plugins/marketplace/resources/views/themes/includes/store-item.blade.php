@php
    $storeAvatarHue = crc32('store-avatar-' . $store->id) % 360;
    $storeAvatarSaturation = 55;
    $storeAvatarLightness = 45;

    // HSL "lightness" doesn't correspond to how bright a color actually looks (e.g.
    // yellow reads much brighter than blue at the same lightness value), so a fixed
    // lightness can't safely assume white text is always readable. Convert to RGB and
    // compute the real WCAG relative luminance, then pick whichever of white/black
    // gives better contrast against this exact background.
    $hslToRgb = function (float $h, float $s, float $l): array {
        $s /= 100;
        $l /= 100;
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [($r + $m) * 255, ($g + $m) * 255, ($b + $m) * 255];
    };

    $relativeLuminance = function (array $rgb): float {
        $channel = function (float $c) {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $channel($rgb[0]) + 0.7152 * $channel($rgb[1]) + 0.0722 * $channel($rgb[2]);
    };

    $storeAvatarLuminance = $relativeLuminance($hslToRgb($storeAvatarHue, $storeAvatarSaturation, $storeAvatarLightness));
    $contrastWithWhite = 1.05 / ($storeAvatarLuminance + 0.05);
    $contrastWithBlack = ($storeAvatarLuminance + 0.05) / 0.05;
    $storeAvatarTextColor = $contrastWithWhite >= $contrastWithBlack ? '#ffffff' : '#000000';
@endphp

@once
    <style>
        /* This component has no CSS anywhere else in the codebase/theme, so it's fully
           self-contained here - including defensive resets on layout properties in case
           some unrelated global rule happens to match these class names. */
        .card.bb-store-item {
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100%;
            padding: 0;
            transition: box-shadow .15s ease;
        }
        .card.bb-store-item:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
        }
        .bb-store-item-cover {
            position: relative !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 100%;
            aspect-ratio: 16 / 9;
            background-color: #f4f5f7;
            background-repeat: no-repeat;
            background-position: center;
            border-top-left-radius: var(--bs-card-border-radius, 0.375rem);
            border-top-right-radius: var(--bs-card-border-radius, 0.375rem);
        }
        .bb-store-item-cover-logo {
            /* The store's profile logo, not a wide banner photo - contain and pad it
               instead of cropping it edge-to-edge like a cover image. */
            background-size: contain;
            padding: 16px;
        }
        .bb-store-item-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            text-transform: uppercase;
            user-select: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }
        /* Overlay badges sit on top of a photo, a color fallback, or the avatar bubble -
           an unpredictable mix of backgrounds - so instead of computing contrast per
           store like the avatar does, use a translucent dark scrim + white text, which
           reads clearly against any of them. */
        .bb-store-item-badge {
            position: absolute !important;
            top: 8px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            background-color: rgba(0, 0, 0, .55);
            color: #fff;
            font-size: .75rem;
            line-height: 1.4;
            white-space: nowrap;
        }
        .bb-store-item-badge-rating {
            left: 8px;
        }
        .bb-store-item-badge-visits {
            right: 8px;
        }
        .bb-store-item-content {
            position: static !important;
            flex: 1 1 auto;
            padding: 14px;
        }
        .bb-store-item-content h4 {
            font-size: 1rem;
            margin-bottom: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .bb-store-item-info {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: .8125rem;
            color: #6c757d;
            margin-bottom: 4px;
        }
        .bb-store-item-info:last-child {
            margin-bottom: 0;
        }
        .bb-store-item-info span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .bb-store-item-info svg {
            flex-shrink: 0;
        }
        .bb-store-item-footer {
            position: static !important;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 14px 14px;
        }
        .bb-store-item-action {
            position: static !important;
            flex-shrink: 0;
            width: 100%;
        }
        .bb-store-item-action .btn {
            width: 100%;
        }

        @media (max-width: 575.98px) {
            .bb-store-item-badge {
                font-size: .6875rem;
                padding: 2px 7px;
            }
            .bb-store-item-avatar {
                width: 52px;
                height: 52px;
                font-size: 1.25rem;
            }
        }
    </style>
@endonce

<div class="card bb-store-item">
    <a
        href="{{ $store->url }}"
        class="bb-store-item-cover @if ($store->logo) bb-store-item-cover-logo @endif"
        @if ($store->logo)
            style="background-image: url('{{ RvMedia::getImageUrl($store->logo) }}');"
        @else
            style="background-color: hsl({{ $storeAvatarHue }}, 35%, 94%);"
        @endif
    >
        @if (EcommerceHelper::isReviewEnabled() && (!EcommerceHelper::hideRatingWhenNoReviews() || $store->reviews->count() > 0))
            <span class="bb-store-item-badge bb-store-item-badge-rating">
                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $store->reviews()->avg('star'), 'size' => 50])
                {{ number_format($store->reviews->count()) }}
            </span>
        @endif

        <span class="bb-store-item-badge bb-store-item-badge-visits">
            <x-core::icon name="ti ti-eye" />
            {{ number_format($store->visitsCount()) }}
        </span>

        @unless ($store->logo)
            <span
                class="bb-store-item-avatar"
                style="background-color: hsl({{ $storeAvatarHue }}, {{ $storeAvatarSaturation }}%, {{ $storeAvatarLightness }}%); color: {{ $storeAvatarTextColor }};"
            >{{ mb_substr($store->name, 0, 1) }}</span>
        @endunless
    </a>

    <div class="bb-store-item-content">
        <a href="{{ $store->url }}">
            <h4 title="{{ $store->name }}">
                {{ $store->name }}
                {!! $store->badge !!}
            </h4>
        </a>

        @if ($store->establishment_date)
            <p class="bb-store-item-info">
                <x-core::icon name="ti ti-calendar" />
                {{ __('Estd - ') }}{{ \Carbon\Carbon::parse($store->establishment_date)->format('M Y') }}
            </p>
        @endif

        @if (! MarketplaceHelper::hideStoreAddress() && $store->full_address)
            <p class="bb-store-item-info" title="{{ $store->full_address }}">
                <x-core::icon name="ti ti-map-pin" />
                <span>{{ $store->full_address }}</span>
            </p>
        @endif

        @if (! MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
            <p class="bb-store-item-info">
                <x-core::icon name="ti ti-phone" />
                <a href="tel:{{ $store->phone }}" class="text-reset">{{ $store->phone }}</a>
            </p>
        @endif

        @if (! MarketplaceHelper::hideStoreEmail() && $store->email)
            <p class="bb-store-item-info" title="{{ $store->email }}">
                <x-core::icon name="ti ti-mail" />
                <a href="mailto:{{ $store->email }}" class="text-reset">{{ $store->email }}</a>
            </p>
        @endif
    </div>

    <div class="bb-store-item-footer">
        <div class="bb-store-item-action">
            <a href="{{ $store->url }}" class="btn btn-primary">
                <x-core::icon name="ti ti-building-store" />
                {{ __('Visit Store') }}
            </a>
        </div>
    </div>
</div>
