@php
    $warrantyOption = $product->exists ? $product->options()->where('is_warranty', true)->with('values')->first() : null;
    $warrantyPlans = old('warranty_plans', $warrantyOption?->values->where('warranty_months', '>', 0)->map(fn ($value) => ['name' => $value->option_value, 'months' => $value->warranty_months, 'price' => $value->affect_price])->values()->all() ?? []);
@endphp
<div data-warranty-editor>
    <input type="hidden" name="has_warranty_settings" value="1">
    <input type="hidden" name="warranty_enabled" value="0">
    <label class="form-check form-switch mb-3"><input type="checkbox" class="form-check-input" name="warranty_enabled" value="1" data-warranty-enable @checked(old('warranty_enabled', (bool) $warrantyOption))><span class="form-check-label">{{ __('Warranty available') }}</span></label>
    <p class="text-muted small">{{ __('Optional warranty add-ons for physical products. Charges apply to every unit and follow the product’s tax and discount rules. Coverage starts from delivery.') }}</p>
    <div data-warranty-fields>
        <label class="form-label" for="included-warranty">{{ __('Included manufacturer warranty (optional)') }}</label>
        <input class="form-control mb-3" id="included-warranty" name="included_warranty" maxlength="160" value="{{ old('included_warranty', $warrantyOption?->included_warranty) }}" placeholder="{{ __('e.g. 1 year manufacturer warranty included') }}">
        <div data-warranty-plans>
            @foreach ($warrantyPlans as $index => $plan)
                <div class="row g-2 mb-3 align-items-end" data-warranty-plan>
                    <div class="col-md-5"><label class="form-label">{{ __('Plan name') }}</label><input class="form-control" name="warranty_plans[{{ $index }}][name]" maxlength="60" value="{{ $plan['name'] }}" required></div>
                    <div class="col-md-2"><label class="form-label">{{ __('Months') }}</label><input class="form-control" name="warranty_plans[{{ $index }}][months]" type="number" min="1" max="120" value="{{ $plan['months'] }}" required></div>
                    <div class="col-md-3"><label class="form-label">{{ __('Extra price / unit') }}</label><input class="form-control" name="warranty_plans[{{ $index }}][price]" type="number" min="0.01" max="1000000" step="0.01" value="{{ $plan['price'] }}" required></div>
                    <div class="col-md-2"><button class="btn btn-outline-danger" type="button" data-warranty-remove>{{ __('Remove') }}</button></div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-primary mb-3" data-warranty-add>{{ __('Add warranty plan') }}</button>
        <label class="form-label" for="warranty-terms">{{ __('Coverage and exclusions') }}</label>
        <textarea class="form-control" id="warranty-terms" name="warranty_terms" maxlength="3000" rows="3" placeholder="{{ __('Describe what is covered, exclusions, claim contact, and whether this extends or overlaps manufacturer coverage.') }}">{{ old('warranty_terms', $warrantyOption?->warranty_terms) }}</textarea>
        <small class="text-muted">{{ __('The selected plan and these terms are saved with the order. Maximum 10 plans. Customers start with no additional warranty selected.') }}</small>
    </div>
    <p class="text-muted small mt-3 mb-0">{{ __('For color choices with separate stock, SKU, and images, use Color attributes and product variations. Warranty is added on top of the selected color’s price.') }}</p>
</div>
<template id="warranty-plan-template">
    <div class="row g-2 mb-3 align-items-end" data-warranty-plan>
        <div class="col-md-5"><label class="form-label">{{ __('Plan name') }}</label><input class="form-control" data-name="name" maxlength="60" required placeholder="{{ __('e.g. 1 year extended warranty') }}"></div>
        <div class="col-md-2"><label class="form-label">{{ __('Months') }}</label><input class="form-control" data-name="months" type="number" min="1" max="120" value="12" required></div>
        <div class="col-md-3"><label class="form-label">{{ __('Extra price / unit') }}</label><input class="form-control" data-name="price" type="number" min="0.01" max="1000000" step="0.01" required></div>
        <div class="col-md-2"><button class="btn btn-outline-danger" type="button" data-warranty-remove>{{ __('Remove') }}</button></div>
    </div>
</template>
<script>
(function () {
    var editor = document.querySelector('[data-warranty-editor]');
    var enable = editor.querySelector('[data-warranty-enable]');
    var fields = editor.querySelector('[data-warranty-fields]');
    var list = editor.querySelector('[data-warranty-plans]');
    var index = {{ count($warrantyPlans) }};
    function update() {
        fields.hidden = !enable.checked;
        fields.querySelectorAll('input, textarea, button').forEach(function (field) { field.disabled = !enable.checked; });
    }
    editor.querySelector('[data-warranty-add]').addEventListener('click', function () {
        if (list.children.length >= 10) return;
        var row = document.getElementById('warranty-plan-template').content.cloneNode(true);
        row.querySelectorAll('[data-name]').forEach(function (field) { field.name = 'warranty_plans[' + index + '][' + field.dataset.name + ']'; });
        index++;
        list.appendChild(row);
        update();
    });
    editor.addEventListener('click', function (event) { if (event.target.closest('[data-warranty-remove]')) event.target.closest('[data-warranty-plan]').remove(); });
    enable.addEventListener('change', update);
    update();
})();
</script>
