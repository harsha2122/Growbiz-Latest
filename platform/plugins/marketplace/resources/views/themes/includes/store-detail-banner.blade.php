@once
    <style>
        .bb-shop-banner-logo {
            display: block;
            box-sizing: border-box;
            width: 100px;
            min-width: 100px;
            max-width: 100px;
            height: 100px;
            min-height: 100px;
            max-height: 100px;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            border: 4px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .15);
            background-color: #fff;
            flex-shrink: 0;
        }

        @media (max-width: 767px) {
            .bb-shop-banner-logo {
                width: 72px;
                min-width: 72px;
                max-width: 72px;
                height: 72px;
                min-height: 72px;
                max-height: 72px;
                border-width: 3px;
            }
        }
    </style>
@endonce

<div class="bb-shop-banner" @if ($coverImage) style="background-image: url('{{ RvMedia::getImageUrl($coverImage) }}');" @endif>
    <div class="bb-shop-banner-overlay"></div>
    <div class="container bb-shop-banner-content">
        {{ RvMedia::image($store->logo, $store->name, useDefaultImage: true, attributes: ['class' => 'bb-shop-banner-logo']) }}

        <div class="bb-shop-banner-info">
            <h2 class="bb-shop-banner-name">
                {{ $store->name }}
                {!! $store->badge !!}
            </h2>

            @if (EcommerceHelper::isReviewEnabled() && (!EcommerceHelper::hideRatingWhenNoReviews() || $store->reviews->count() > 0))
                <div class="bb-shop-banner-rating">
                    @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $store->reviews()->avg('star'), 'size' => 80])
                    <small>{{ __('(:count reviews)', ['count' => number_format($store->reviews->count())]) }}</small>
                </div>
            @endif

            <div class="bb-shop-banner-visits d-flex align-items-center gap-1">
                <x-core::icon name="ti ti-eye" />
                {{ __(':count store visits', ['count' => number_format($store->visitsCount())]) }}
            </div>

            @if ($store->establishment_date)
                <div class="bb-shop-banner-established d-flex align-items-center gap-1">
                    <x-core::icon name="ti ti-calendar" />
                    {{ __('Since ') }}{{ \Carbon\Carbon::parse($store->establishment_date)->format('M Y') }}
                </div>
            @endif

            @if ($store->full_address || $store->phone || $store->email)
                <div class="bb-shop-banner-contact">
                    @if (!MarketplaceHelper::hideStoreAddress() && $store->full_address)
                        <div class="bb-shop-banner-address d-flex gap-1">
                            <x-core::icon name="ti ti-map-pin" />
                            {{ $store->full_address }}
                        </div>
                    @endif

                    @if (!MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                        <div class="bb-shop-banner-phone d-flex gap-1">
                            <x-core::icon name="ti ti-phone" />
                            <a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
                        </div>
                    @endif

                    @if (!MarketplaceHelper::hideStoreEmail() && $store->email)
                        <div class="bb-shop-banner-address d-flex gap-1">
                            <x-core::icon name="ti ti-mail" />
                            <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
                        </div>
                    @endif
                </div>
            @endif

            @if ($store->description)
                <div class="bb-shop-banner-description ck-content">
                    {!! BaseHelper::clean($store->description) !!}
                </div>
            @endif

            @if (!MarketplaceHelper::hideStoreSocialLinks() && ($socials = $store->getMetaData('social_links', true)))
                <ul class="bb-shop-banner-socials">
                    @foreach (MarketplaceHelper::getAllowedSocialLinks() as $key => $social)
                        @continue(! Arr::get($socials, $key))

                        <li>
                            <a href="{{ Arr::get($social, 'url') . Arr::get($socials, $key) }}" target="_blank">
                                @if ($icon = Arr::get($social, 'icon'))
                                    <x-core::icon :name="'ti ti-brand-' . $icon" />
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
