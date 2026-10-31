@once
<style>
.bb-warranty {padding:16px;border:1px solid #e5e8ec;border-radius:10px;margin:16px 0;background:#fff;}
.bb-warranty h3 {font-size:15px;font-weight:600;margin:0 0 4px;}
.bb-warranty-note {font-size:12px;color:#6b7280;margin:0 0 12px;line-height:1.6;}
.bb-warranty-choice {display:flex;align-items:center;gap:10px;padding:10px 0;border-top:1px solid #f0f1f3;cursor:pointer;font-size:13px;}
.bb-warranty-choice input {accent-color:var(--tp-theme-primary,#d77d68);flex-shrink:0;}
.bb-warranty-choice span {flex:1;min-width:0;overflow-wrap:anywhere;}
.bb-warranty-choice strong {font-size:13px;white-space:nowrap;}
.bb-warranty-choice small {display:block;color:#6b7280;font-size:11px;}
.bb-warranty-total {display:flex;justify-content:space-between;gap:12px;margin:8px 0 0;padding-top:12px;border-top:1px solid #e5e8ec;font-size:13px;}
.bb-warranty details {font-size:12px;color:#6b7280;margin-top:10px;}
.bb-warranty details p {font-size:inherit;white-space:pre-line;overflow-wrap:anywhere;margin:8px 0 0;}
@media(max-width:575px){.bb-warranty{padding:13px;}}
</style>
@endonce
<section class="bb-warranty product-option" data-warranty-picker aria-label="{{ __('Warranty options') }}">
    <h3>{{ __('Add warranty protection') }}</h3>
    <p class="bb-warranty-note">{{ $option->included_warranty ?: __('Optional protection. No additional warranty is selected by default.') }}</p>
    <input type="hidden" name="options[{{ $option->id }}][option_type]" value="radio">
    @foreach ($option->values as $value)
        <label class="bb-warranty-choice">
            <input type="radio" name="options[{{ $option->id }}][values]" value="{{ $value->option_value }}" data-warranty-price="{{ format_price($value->affect_price, null, true) }}" @checked($loop->first)>
            <span>{{ $value->option_value }}@if ($value->warranty_months)<small>{{ trans_choice(':count month of coverage|:count months of coverage', $value->warranty_months, ['count' => $value->warranty_months]) }}</small>@endif</span>
            <strong>{{ $value->affect_price > 0 ? '+ ' . format_price($value->affect_price) : __('No extra cost') }}</strong>
        </label>
    @endforeach
    <div class="bb-warranty-total"><span>{{ __('Unit price with selected options') }}</span><strong data-warranty-total aria-live="polite">{{ $product->price()->displayAsText() }}</strong></div>
    <small class="bb-warranty-note d-block mt-2 mb-0">{{ __('Warranty is charged per unit. Tax and discounts are calculated at checkout. Coverage begins on delivery.') }}</small>
    @if ($option->warranty_terms)
        <details><summary>{{ __('Coverage & exclusions') }}</summary><p>{{ $option->warranty_terms }}</p></details>
    @endif
</section>
<script>
(function () {
    function init() {
        document.querySelectorAll('[data-warranty-picker]').forEach(function (picker) {
            if (picker.dataset.ready) return;
            picker.dataset.ready = '1';
            var form = picker.closest('form');
            var price = form.querySelector('[data-bb-value="product-price"]') || document.querySelector('[data-bb-value="product-price"]');
            var formatter = new Intl.NumberFormat(document.documentElement.lang || 'en-IN', {style:'currency', currency: {{ Illuminate\Support\Js::from(get_application_currency()->title) }} });
            function update() {
                var displayed = price ? price.textContent.split({{ Illuminate\Support\Js::from(get_ecommerce_setting('thousands_separator', ',')) }}).join('').replace({{ Illuminate\Support\Js::from(get_ecommerce_setting('decimal_separator', '.')) }}, '.') : '';
                var base = price ? Number(displayed.replace(/[^0-9.-]/g, '')) : {{ (float) format_price($product->price()->getPrice(), null, true) }};
                var extra = 0;
                form.querySelectorAll('[data-warranty-price]:checked').forEach(function (input) { extra += Number(input.dataset.warrantyPrice) || 0; });
                form.querySelectorAll('[data-extra-price]:checked').forEach(function (input) { extra += Number(input.dataset.extraPrice) || 0; });
                picker.querySelector('[data-warranty-total]').textContent = formatter.format(base + extra);
            }
            form.addEventListener('change', update);
            if (price) new MutationObserver(update).observe(price, {childList:true,characterData:true,subtree:true});
            update();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
