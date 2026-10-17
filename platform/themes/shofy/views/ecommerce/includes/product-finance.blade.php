@if ($product->finance_available)
    <div class="tp-product-finance mt-15 mb-15 p-3" style="border:1px solid #e2e2e2;border-radius:8px;background:#f8fafc;">
        <div class="d-flex align-items-center gap-2 mb-2">
            <x-core::icon name="ti ti-credit-card" />
            <strong>{{ __('Finance Available') }}</strong>
        </div>
        <ul class="list-unstyled mb-0 small text-muted">
            @if ($product->finance_provider)
                <li>{{ __('Provider') }}: {{ $product->finance_provider }}</li>
            @endif
            @if ($product->finance_interest_rate)
                <li>{{ __('Interest Rate') }}: {{ rtrim(rtrim(number_format((float) $product->finance_interest_rate, 2), '0'), '.') }}%</li>
            @endif
            @if ($product->finance_tenure_options)
                <li>{{ __('Tenure') }}: {{ $product->finance_tenure_options }}</li>
            @endif
            @if ($product->finance_down_payment_percent)
                <li>{{ __('Down Payment') }}: {{ rtrim(rtrim(number_format((float) $product->finance_down_payment_percent, 2), '0'), '.') }}%</li>
            @endif
            @if ($product->finance_eligibility_notes)
                <li>{{ $product->finance_eligibility_notes }}</li>
            @endif
        </ul>
    </div>
@endif
