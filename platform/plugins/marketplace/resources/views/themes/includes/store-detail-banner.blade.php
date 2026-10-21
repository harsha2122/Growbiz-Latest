@once
    <style>
        .bb-store-header {
            margin-bottom: 24px;
        }

        .bb-store-header-cover {
            width: 100%;
            height: 280px;
            background-color: var(--primary-color);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .bb-store-header-row {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: -70px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eceef1;
        }

        .bb-store-header-avatar {
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

        .bb-store-header-info {
            flex: 1 1 auto;
            min-width: 0;
            padding-bottom: 6px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .bb-store-header-identity {
            min-width: 0;
        }

        .bb-store-header-actions .tp-enquiry-btn {
            width: auto !important;
            margin: 0 !important;
            padding: 8px 20px;
            border-radius: 6px;
        }

        @media (max-width: 767px) {
            .bb-store-header-info {
                align-items: flex-start;
            }
        }

        .bb-store-header-name {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #050505;
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 6px;
            text-align: left;
        }

        .bb-store-header-name .store-verified-badge {
            margin-left: 0;
        }

        .bb-store-header-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px 16px;
            color: #65676b;
            font-size: 14px;
        }

        .bb-store-header-meta > div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .bb-store-header-rating {
            display: flex;
            align-items: center;
            gap: .35rem;
            justify-content: flex-start;
        }

        .bb-store-header-details {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 24px;
            color: #444;
            font-size: 14px;
        }

        .bb-store-header-details > div {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .bb-store-header-description {
            flex-basis: 100%;
            color: #444;
            text-align: left;
        }

        .bb-store-header-socials {
            display: flex;
            gap: .5rem;
            list-style-type: none;
            margin: 0;
            padding: 0;
            justify-content: flex-start;
        }

        .bb-store-header-socials a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: #f0f2f5;
            color: #65676b;
        }

        .bb-store-header-socials a:hover {
            background-color: var(--primary-color);
            color: #fff;
        }

        @media (max-width: 767px) {
            .bb-store-header-cover {
                height: 160px;
            }

            .bb-store-header-row {
                margin-top: -40px;
                gap: 12px;
            }

            .bb-store-header-avatar {
                width: 90px;
                min-width: 90px;
                max-width: 90px;
                height: 90px;
                min-height: 90px;
                max-height: 90px;
                border-width: 3px;
            }

            .bb-store-header-name {
                font-size: 19px;
            }

            .bb-store-header-info {
                padding-bottom: 0;
            }
        }
    </style>
@endonce

<div class="bb-store-header">
    <div class="bb-store-header-cover" @if ($coverImage) style="background-image: url('{{ RvMedia::getImageUrl($coverImage) }}');" @endif></div>

    <div class="container">
        <div class="bb-store-header-row">
            {{ RvMedia::image($store->logo, $store->name, useDefaultImage: true, attributes: ['class' => 'bb-store-header-avatar']) }}

            <div class="bb-store-header-info">
                <div class="bb-store-header-identity">
                    <h2 class="bb-store-header-name">
                        {{ $store->name }}
                        {!! $store->badge !!}
                    </h2>

                    <div class="bb-store-header-meta">
                        @if (EcommerceHelper::isReviewEnabled() && (!EcommerceHelper::hideRatingWhenNoReviews() || $store->reviews->count() > 0))
                            <div class="bb-store-header-rating">
                                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $store->reviews()->avg('star'), 'size' => 80])
                                <small>{{ __('(:count reviews)', ['count' => number_format($store->reviews->count())]) }}</small>
                            </div>
                        @endif

                        <div class="bb-store-header-visits">
                            <x-core::icon name="ti ti-eye" />
                            {{ __(':count store visits', ['count' => number_format($store->visitsCount())]) }}
                        </div>

                        @if ($store->establishment_date)
                            <div class="bb-store-header-established">
                                <x-core::icon name="ti ti-calendar" />
                                {{ __('Since ') }}{{ \Carbon\Carbon::parse($store->establishment_date)->format('M Y') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bb-store-header-actions">
                    @includeIf(MarketplaceHelper::viewPath('includes.enquiry-button'), ['type' => 'vendor', 'store' => $store, 'product' => null, 'inline' => true])
                </div>
            </div>
        </div>

        @php
            $socials = !MarketplaceHelper::hideStoreSocialLinks() ? $store->getMetaData('social_links', true) : null;
        @endphp

        @if ($store->full_address || $store->phone || $store->email || $store->description || ! empty($socials))
            <div class="bb-store-header-details">
                @if (!MarketplaceHelper::hideStoreAddress() && $store->full_address)
                    <div class="bb-store-header-address">
                        <x-core::icon name="ti ti-map-pin" />
                        {{ $store->full_address }}
                    </div>
                @endif

                @if (!MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                    <div class="bb-store-header-phone">
                        <x-core::icon name="ti ti-phone" />
                        <a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
                    </div>
                @endif

                @if (!MarketplaceHelper::hideStoreEmail() && $store->email)
                    <div class="bb-store-header-email">
                        <x-core::icon name="ti ti-mail" />
                        <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
                    </div>
                @endif

                @if ($store->description)
                    <div class="bb-store-header-description ck-content">
                        {!! BaseHelper::clean($store->description) !!}
                    </div>
                @endif

                @if (!MarketplaceHelper::hideStoreSocialLinks() && ! empty($socials))
                    <ul class="bb-store-header-socials">
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
