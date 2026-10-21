@once
    <style>
        .bb-shop-banner-wrap {
            margin-bottom: 24px;
        }

        .bb-shop-cover {
            width: 100%;
            height: 280px;
            background-color: var(--primary-color);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .bb-shop-banner-row {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: -70px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eceef1;
        }

        .bb-shop-banner-logo {
            display: block;
            box-sizing: border-box;
            width: 160px;
            min-width: 160px;
            max-width: 160px;
            height: 160px;
            min-height: 160px;
            max-height: 160px;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            border: 6px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .15);
            background-color: #fff;
            flex-shrink: 0;
        }

        .bb-shop-banner-info {
            flex: 1 1 auto;
            min-width: 0;
            padding-bottom: 6px;
        }

        .bb-shop-banner-name {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #050505;
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .bb-shop-banner-name .store-verified-badge {
            margin-left: 0;
        }

        .bb-shop-banner-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px 16px;
            color: #65676b;
            font-size: 14px;
        }

        .bb-shop-banner-meta > div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .bb-shop-banner-rating {
            gap: .35rem;
        }

        .bb-shop-banner-details {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 24px;
            color: #444;
            font-size: 14px;
        }

        .bb-shop-banner-details > div {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .bb-shop-banner-description {
            flex-basis: 100%;
            color: #444;
        }

        .bb-shop-banner-socials {
            display: flex;
            gap: .5rem;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .bb-shop-banner-socials a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: #f0f2f5;
            color: #65676b;
        }

        .bb-shop-banner-socials a:hover {
            background-color: var(--primary-color);
            color: #fff;
        }

        @media (max-width: 767px) {
            .bb-shop-cover {
                height: 160px;
            }

            .bb-shop-banner-row {
                margin-top: -40px;
                gap: 12px;
            }

            .bb-shop-banner-logo {
                width: 90px;
                min-width: 90px;
                max-width: 90px;
                height: 90px;
                min-height: 90px;
                max-height: 90px;
                border-width: 3px;
            }

            .bb-shop-banner-name {
                font-size: 19px;
            }

            .bb-shop-banner-info {
                padding-bottom: 0;
            }
        }
    </style>
@endonce

<div class="bb-shop-banner-wrap">
    <div class="bb-shop-cover" @if ($coverImage) style="background-image: url('{{ RvMedia::getImageUrl($coverImage) }}');" @endif></div>

    <div class="container">
        <div class="bb-shop-banner-row">
            {{ RvMedia::image($store->logo, $store->name, useDefaultImage: true, attributes: ['class' => 'bb-shop-banner-logo']) }}

            <div class="bb-shop-banner-info">
                <h2 class="bb-shop-banner-name">
                    {{ $store->name }}
                    {!! $store->badge !!}
                </h2>

                <div class="bb-shop-banner-meta">
                    @if (EcommerceHelper::isReviewEnabled() && (!EcommerceHelper::hideRatingWhenNoReviews() || $store->reviews->count() > 0))
                        <div class="bb-shop-banner-rating">
                            @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $store->reviews()->avg('star'), 'size' => 80])
                            <small>{{ __('(:count reviews)', ['count' => number_format($store->reviews->count())]) }}</small>
                        </div>
                    @endif

                    <div class="bb-shop-banner-visits">
                        <x-core::icon name="ti ti-eye" />
                        {{ __(':count store visits', ['count' => number_format($store->visitsCount())]) }}
                    </div>

                    @if ($store->establishment_date)
                        <div class="bb-shop-banner-established">
                            <x-core::icon name="ti ti-calendar" />
                            {{ __('Since ') }}{{ \Carbon\Carbon::parse($store->establishment_date)->format('M Y') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @php
            $socials = !MarketplaceHelper::hideStoreSocialLinks() ? $store->getMetaData('social_links', true) : null;
        @endphp

        @if ($store->full_address || $store->phone || $store->email || $store->description || ! empty($socials))
            <div class="bb-shop-banner-details">
                @if (!MarketplaceHelper::hideStoreAddress() && $store->full_address)
                    <div class="bb-shop-banner-address">
                        <x-core::icon name="ti ti-map-pin" />
                        {{ $store->full_address }}
                    </div>
                @endif

                @if (!MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                    <div class="bb-shop-banner-phone">
                        <x-core::icon name="ti ti-phone" />
                        <a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
                    </div>
                @endif

                @if (!MarketplaceHelper::hideStoreEmail() && $store->email)
                    <div class="bb-shop-banner-email">
                        <x-core::icon name="ti ti-mail" />
                        <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
                    </div>
                @endif

                @if ($store->description)
                    <div class="bb-shop-banner-description ck-content">
                        {!! BaseHelper::clean($store->description) !!}
                    </div>
                @endif

                @if (!MarketplaceHelper::hideStoreSocialLinks() && ! empty($socials))
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
        @endif
    </div>
</div>
