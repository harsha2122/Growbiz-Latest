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
        }
        .bb-store-item-cover {
            position: relative !important;
            display: block !important;
            width: 100%;
            aspect-ratio: 16 / 9;
            background-color: #f1f2f4;
            background-image: linear-gradient(135deg, #e9ecf2 0%, #f6f7fa 100%);
            background-size: cover;
            background-position: center;
            border-top-left-radius: var(--bs-card-border-radius, 0.375rem);
            border-top-right-radius: var(--bs-card-border-radius, 0.375rem);
        }
        .bb-store-item-logo {
            position: absolute !important;
            left: 16px;
            bottom: -32px;
            z-index: 2;
            flex-shrink: 0;
        }
        .bb-store-item-logo img {
            position: static !important;
            display: block;
            box-sizing: border-box;
            width: 72px;
            min-width: 72px;
            max-width: 72px;
            height: 72px;
            min-height: 72px;
            max-height: 72px;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            border: 3px solid #fff;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .15);
            background-color: #fff;
        }
        .bb-store-item-content {
            position: static !important;
            flex: 1 1 auto;
            padding: 40px 16px 16px;
        }
        .bb-store-item-stats {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px 12px;
        }
        .bb-store-item-footer {
            position: static !important;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            padding: 16px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }
        .bb-store-item-action {
            position: static !important;
            flex-shrink: 0;
            width: 100%;
        }
        .bb-store-item-action .btn {
            width: 100%;
        }
    </style>
@endonce

<div class="card bb-store-item">
    <a href="{{ $store->url }}" class="bb-store-item-cover" @if ($store->cover_image) style="background-image: url('{{ RvMedia::getImageUrl($store->cover_image) }}');" @endif>
        <div class="bb-store-item-logo">
            {{ RvMedia::image($store->logo, $store->name, useDefaultImage: true) }}
        </div>
    </a>

    <div class="bb-store-item-content">
        <a href="{{ $store->url }}">
            <h4>
                {{ $store->name }}
                {!! $store->badge !!}
            </h4>
        </a>

        <div class="bb-store-item-stats mb-1">
            @if (EcommerceHelper::isReviewEnabled() && (!EcommerceHelper::hideRatingWhenNoReviews() || $store->reviews->count() > 0))
                <div class="d-flex align-items-center gap-1 bb-store-item-rating">
                    @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $store->reviews()->avg('star')])
                    <a href="{{ $store->url }}" class="small">{{ __('(:count reviews)', ['count' => number_format($store->reviews->count())]) }}</a>
                </div>
            @endif

            <div class="d-flex align-items-center gap-1 small text-muted">
                <x-core::icon name="ti ti-eye" />
                {{ __(':count visits', ['count' => number_format($store->visitsCount())]) }}
            </div>
        </div>

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
        <div class="bb-store-item-action">
            <a href="{{ $store->url }}" class="btn btn-primary">
                <x-core::icon name="ti ti-building-store" />
                {{ __('Visit Store') }}
            </a>
        </div>
    </div>
</div>
