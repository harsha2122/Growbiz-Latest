@php
    $highlights = [];

    if ($store->completed_projects !== null) {
        $highlights[] = ['icon' => 'ti ti-briefcase', 'value' => number_format($store->completed_projects), 'label' => __('Completed projects')];
    }

    if ($store->happy_customers !== null) {
        $highlights[] = ['icon' => 'ti ti-heart-handshake', 'value' => number_format($store->happy_customers), 'label' => __('Happy customers')];
    }

    if ($store->years_of_experience !== null) {
        $highlights[] = ['icon' => 'ti ti-award', 'value' => number_format($store->years_of_experience), 'label' => __('Years of experience')];
    }

    if (! MarketplaceHelper::hideStoreAddress() && filled($store->public_location)) {
        $highlights[] = ['icon' => 'ti ti-map-pin', 'value' => $store->public_location, 'label' => __('Based in'), 'location' => true];
    }
@endphp

@if ($store->is_minimal_profile && $highlights)
    @once
        <style>
            .bb-store-highlights {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin: 20px 0 0;
            }

            .bb-store-highlight {
                display: flex;
                align-items: center;
                gap: 14px;
                flex: 1 1 200px;
                min-width: 0;
                padding: 18px 20px;
                border: 1px solid #e9ecef;
                border-radius: 14px;
                background: linear-gradient(135deg, #f8fafb, #fff);
            }

            .bb-store-highlight-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 44px;
                width: 44px;
                height: 44px;
                border-radius: 12px;
                color: var(--primary-color, #136b58);
                background: #fff;
                border: 1px solid #e9ecef;
            }

            .bb-store-highlight-icon .icon { width: 23px; height: 23px; }
            .bb-store-highlight-copy { min-width: 0; margin: 0; }
            .bb-store-highlight dt { margin: 0 0 4px; color: #626970; font-size: 12px; line-height: 1.4; }
            .bb-store-highlight dd { margin: 0; color: #182026; font-size: 26px; font-weight: 700; line-height: 1.2; letter-spacing: -.5px; overflow-wrap: anywhere; }
            .bb-store-highlight--location dd { font-size: 17px; letter-spacing: 0; line-height: 1.4; }

            @media (max-width: 575px) {
                .bb-store-highlights { gap: 8px; }
                .bb-store-highlight { flex-basis: calc(50% - 8px); padding: 14px 12px; gap: 10px; }
                .bb-store-highlight-icon { flex-basis: 34px; width: 34px; height: 34px; border-radius: 10px; }
                .bb-store-highlight-icon .icon { width: 20px; height: 20px; }
                .bb-store-highlight dd { font-size: 22px; }
                .bb-store-highlight--location { flex-basis: 100%; }
                .bb-store-highlight--location dd { font-size: 16px; }
            }
        </style>
    @endonce

    <div class="bb-store-highlights" role="list" aria-label="{{ __('Store highlights') }}">
        @foreach ($highlights as $highlight)
            <div @class(['bb-store-highlight', 'bb-store-highlight--location' => $highlight['location'] ?? false]) role="listitem">
                <span class="bb-store-highlight-icon" aria-hidden="true">
                    <x-core::icon :name="$highlight['icon']" />
                </span>
                <dl class="bb-store-highlight-copy">
                    <dt>{{ $highlight['label'] }}</dt>
                    <dd>{{ $highlight['value'] }}</dd>
                </dl>
            </div>
        @endforeach
    </div>
@endif
