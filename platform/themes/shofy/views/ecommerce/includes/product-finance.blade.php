@if ($product->finance_available)
    @php
        $formatFinancePercent = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
        $hasFinanceRate = $product->finance_interest_rate !== null && $product->finance_interest_rate !== '';
        $hasFinanceDeposit = $product->finance_down_payment_percent !== null && $product->finance_down_payment_percent !== '';
        $financeTenures = collect(preg_split('/[,;\r\n]+/', (string) $product->finance_tenure_options))
            ->map(fn ($tenure) => trim($tenure))
            ->filter(fn ($tenure) => $tenure !== '')
            ->unique()
            ->values();
    @endphp

    @once
        <style>
            .tp-product-finance { min-width: 0; margin: 16px 0; padding: 16px; border: 1px solid #e7e9ed; border-radius: 10px; background: #fff; overflow-wrap: anywhere; }
            .tp-product-finance-header { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
            .tp-product-finance-icon { display: inline-flex; flex-shrink: 0; color: var(--tp-theme-primary, #d77d68); }
            .tp-product-finance-icon .icon { width: 19px; height: 19px; }
            .tp-product-finance-heading { display: flex; align-items: baseline; flex-wrap: wrap; gap: 4px 8px; min-width: 0; }
            .tp-product-finance-title { margin: 0; color: #182026; font-size: 14px; font-weight: 600; line-height: 1.5; }
            .tp-product-finance-provider { margin: 0; color: #707680; font-size: 12px; line-height: 1.5; }
            .tp-product-finance-provider strong { font-weight: 400; }
            .tp-product-finance-metrics { display: flex; flex-wrap: wrap; gap: 8px 24px; margin: 0; }
            .tp-product-finance-metric { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 6px; min-width: 0; }
            .tp-product-finance-metric dt { margin: 0; color: #707680; font-size: 12px; font-weight: 400; line-height: 1.5; }
            .tp-product-finance-metric dd { margin: 0; color: #182026; font-size: 15px; font-weight: 600; line-height: 1.5; }
            .tp-product-finance-metric dd span { font-size: inherit; }
            .tp-product-finance-tenures { display: flex; align-items: baseline; flex-wrap: wrap; gap: 6px 10px; margin-top: 10px; }
            .tp-product-finance-label { margin: 0; color: #707680; font-size: 12px; font-weight: 400; line-height: 1.5; }
            .tp-product-finance-plans { display: flex; flex-wrap: wrap; gap: 5px; margin: 0; padding: 0; list-style: none; }
            .tp-product-finance-plans li { max-width: 100%; padding: 3px 8px; border-radius: 5px; background: #f4f5f7; color: #4b5563; font-size: 11px; font-weight: 500; line-height: 1.5; }
            .tp-product-finance-notes { margin-top: 10px; color: #707680; font-size: 11px; line-height: 1.6; }
            .tp-product-finance-notes p { margin: 0; color: inherit; font-size: inherit; line-height: inherit; white-space: pre-line; }
            @media (max-width: 575px) {
                .tp-product-finance { padding: 13px; }
                .tp-product-finance-metrics { column-gap: 18px; }
            }
        </style>
    @endonce

    <section class="tp-product-finance" aria-label="{{ __('Product finance options') }}">
        <div class="tp-product-finance-header">
            <span class="tp-product-finance-icon" aria-hidden="true"><x-core::icon name="ti ti-credit-card" /></span>
            <div class="tp-product-finance-heading">
                <h3 class="tp-product-finance-title">{{ __('Finance available') }}</h3>
                @if (filled($product->finance_provider))
                    <p class="tp-product-finance-provider">{{ __('Offered by') }} <strong>{{ $product->finance_provider }}</strong></p>
                @endif
            </div>
        </div>

        @if ($hasFinanceRate || $hasFinanceDeposit)
            <dl class="tp-product-finance-metrics">
                @if ($hasFinanceRate)
                    <div class="tp-product-finance-metric">
                        <dt>{{ __('Interest') }}</dt>
                        <dd>{{ $formatFinancePercent($product->finance_interest_rate) }}<span>%</span></dd>
                    </div>
                @endif
                @if ($hasFinanceDeposit)
                    <div class="tp-product-finance-metric">
                        <dt>{{ __('Down payment') }}</dt>
                        <dd>{{ $formatFinancePercent($product->finance_down_payment_percent) }}<span>%</span></dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($financeTenures->isNotEmpty())
            <div class="tp-product-finance-tenures">
                <p class="tp-product-finance-label">{{ __('Tenure') }}</p>
                <ul class="tp-product-finance-plans" aria-label="{{ __('Available finance tenures') }}">
                    @foreach ($financeTenures as $tenure)
                        <li>{{ ctype_digit($tenure) ? trans_choice(':count month|:count months', (int) $tenure, ['count' => $tenure]) : $tenure }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (filled($product->finance_eligibility_notes))
            <div class="tp-product-finance-notes">
                <div>
                    <p>{{ $product->finance_eligibility_notes }}</p>
                </div>
            </div>
        @endif
    </section>
@endif
