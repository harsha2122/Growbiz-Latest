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
            .tp-product-finance {
                box-sizing: border-box;
                min-width: 0;
                margin: 20px 0;
                padding: 22px;
                border: 1px solid #e7e9ed;
                border-radius: 18px;
                background: #fff;
                box-shadow: 0 4px 20px rgba(24, 32, 38, .035);
                overflow-wrap: anywhere;
            }
            .tp-product-finance-header { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
            .tp-product-finance-icon { display: flex; align-items: center; justify-content: center; flex: 0 0 46px; height: 46px; border-radius: 13px; color: var(--tp-theme-primary, #d77d68); background: #fff3ed; }
            .tp-product-finance-icon .icon { width: 24px; height: 24px; }
            .tp-product-finance-heading { min-width: 0; }
            .tp-product-finance-eyebrow { display: block; margin-bottom: 3px; color: #707680; font-size: 10px; font-weight: 600; letter-spacing: 1.1px; line-height: 1.4; text-transform: uppercase; }
            .tp-product-finance-title { margin: 0; color: #182026; font-size: 18px; font-weight: 700; line-height: 1.4; }
            .tp-product-finance-provider { margin: 4px 0 0; color: #626970; font-size: 12px; line-height: 1.5; }
            .tp-product-finance-provider strong { color: #334155; font-weight: 600; }
            .tp-product-finance-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin: 0; }
            .tp-product-finance-metric { min-width: 0; padding: 14px 16px; background: #f7f8fa; border: 1px solid #eef0f3; border-radius: 12px; }
            .tp-product-finance-metric dt { display: flex; align-items: center; gap: 6px; margin: 0 0 8px; color: #626970; font-size: 12px; font-weight: 500; line-height: 1.4; }
            .tp-product-finance-metric dt .icon { width: 16px; height: 16px; flex-shrink: 0; }
            .tp-product-finance-metric dd { margin: 0; color: #182026; font-size: 28px; font-weight: 700; letter-spacing: -.7px; line-height: 1.15; }
            .tp-product-finance-metric dd span { margin-inline-start: 2px; color: #707680; font-size: 17px; font-weight: 500; letter-spacing: 0; }
            .tp-product-finance-tenures { margin-top: 18px; }
            .tp-product-finance-label { margin: 0 0 9px; color: #626970; font-size: 12px; font-weight: 500; line-height: 1.5; }
            .tp-product-finance-plans { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none; }
            .tp-product-finance-plans li { max-width: 100%; padding: 7px 12px; border: 1px solid #e4e7ec; border-radius: 8px; background: #fff; color: #334155; font-size: 12px; font-weight: 600; line-height: 1.4; }
            .tp-product-finance-notes { display: flex; align-items: flex-start; gap: 8px; margin-top: 18px; padding-top: 14px; border-top: 1px solid #eceef1; color: #626970; font-size: 12px; line-height: 1.65; }
            .tp-product-finance-notes > .icon { width: 16px; height: 16px; margin-top: 2px; flex-shrink: 0; }
            .tp-product-finance-notes strong { display: block; margin-bottom: 2px; color: #334155; font-weight: 600; }
            .tp-product-finance-notes p { margin: 0; color: inherit; font-size: inherit; line-height: inherit; white-space: pre-line; }
            @media (max-width: 575px) {
                .tp-product-finance { margin: 16px 0; padding: 16px; border-radius: 14px; }
                .tp-product-finance-header { margin-bottom: 14px; gap: 10px; }
                .tp-product-finance-title { font-size: 17px; }
                .tp-product-finance-metrics { gap: 8px; }
                .tp-product-finance-metric { padding: 12px; }
                .tp-product-finance-metric dd { font-size: 25px; }
                .tp-product-finance-plans { gap: 6px; }
                .tp-product-finance-plans li { padding: 6px 10px; }
            }
        </style>
    @endonce

    <section class="tp-product-finance" aria-label="{{ __('Product finance options') }}">
        <div class="tp-product-finance-header">
            <span class="tp-product-finance-icon" aria-hidden="true"><x-core::icon name="ti ti-credit-card" /></span>
            <div class="tp-product-finance-heading">
                <span class="tp-product-finance-eyebrow">{{ __('Payment options') }}</span>
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
                        <dt><x-core::icon name="ti ti-percentage" aria-hidden="true" /> {{ __('Interest rate') }}</dt>
                        <dd>{{ $formatFinancePercent($product->finance_interest_rate) }}<span>%</span></dd>
                    </div>
                @endif
                @if ($hasFinanceDeposit)
                    <div class="tp-product-finance-metric">
                        <dt><x-core::icon name="ti ti-wallet" aria-hidden="true" /> {{ __('Down payment') }}</dt>
                        <dd>{{ $formatFinancePercent($product->finance_down_payment_percent) }}<span>%</span></dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($financeTenures->isNotEmpty())
            <div class="tp-product-finance-tenures">
                <p class="tp-product-finance-label">{{ __('Tenure options') }}</p>
                <ul class="tp-product-finance-plans" aria-label="{{ __('Available finance tenures') }}">
                    @foreach ($financeTenures as $tenure)
                        <li>{{ ctype_digit($tenure) ? trans_choice(':count month|:count months', (int) $tenure, ['count' => $tenure]) : $tenure }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (filled($product->finance_eligibility_notes))
            <div class="tp-product-finance-notes">
                <x-core::icon name="ti ti-info-circle" aria-hidden="true" />
                <div>
                    <strong>{{ __('Eligibility & details') }}</strong>
                    <p>{{ $product->finance_eligibility_notes }}</p>
                </div>
            </div>
        @endif
    </section>
@endif
