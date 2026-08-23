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
            overflow: hidden;
            padding: 16px;
        }
        .bb-store-item-content {
            position: static !important;
            flex: 1 1 auto;
        }
        .bb-store-item-footer {
            position: static !important;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }
        .bb-store-item-logo {
            position: static !important;
            flex-shrink: 0;
        }
        .bb-store-item-logo img {
            position: static !important;
            display: block;
            box-sizing: border-box;
            width: 56px;
            min-width: 56px;
            max-width: 56px;
            height: 56px;
            min-height: 56px;
            max-height: 56px;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            border: 2px solid #fff;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .12);
            background-color: #fff;
        }
        .bb-store-item-action {
            position: static !important;
            flex-shrink: 0;
        }
    </style>
@endonce

<div class="card bb-store-item">
    <div class="bb-store-item-content">
        <a href="{{ $store->url }}">
            <h4>
                {{ $store->name }}
                {!! $store->badge !!}
            </h4>
        </a>

        @if (EcommerceHelper::isReviewEnabled() && (!EcommerceHelper::hideRatingWhenNoReviews() || $store->reviews->count() > 0))
            <div class="d-flex align-items-center gap-1 bb-store-item-rating">
                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $store->reviews()->avg('star')])
                <a href="{{ $store->url }}" class="small">{{ __('(:count reviews)', ['count' => number_format($store->reviews->count())]) }}</a>
            </div>
        @endif

        @if ($store->establishment_date)
            <p class="bb-store-item-info text-muted small">
                <x-core::icon name="ti ti-calendar" />
                {{ __('Estd - ') }}{{ \Carbon\Carbon::parse($store->establishment_date)->format('M Y') }}
            </p>
        @endif

        @if (! MarketplaceHelper::hideStoreAddress() && $store->full_address)
            <p class="bb-store-item-info text-truncate" title="{{ $store->full_address }}">
                <x-core::icon name="ti ti-map-pin" />{{ $store->full_address }}
            </p>
        @endif

        @if (! MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
            <p class="bb-store-item-info">
                <x-core::icon name="ti ti-phone" />
                <a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
            </p>
        @endif

        @if (! MarketplaceHelper::hideStoreEmail() && $store->email)
            <p class="bb-store-item-info">
                <x-core::icon name="ti ti-mail" />
                {{ Html::mailto($store->email) }}
            </p>
        @endif
    </div>

    <div class="bb-store-item-footer">
        <div class="bb-store-item-logo">
            <a href="{{ $store->url }}">
                {{ RvMedia::image($store->logo, $store->name, useDefaultImage: true) }}
            </a>
        </div>

        <div class="bb-store-item-action">
            <a href="{{ $store->url }}" class="btn btn-primary">
                <x-core::icon name="ti ti-building-store" />
                {{ __('Visit Store') }}
            </a>
        </div>
    </div>
</div>
