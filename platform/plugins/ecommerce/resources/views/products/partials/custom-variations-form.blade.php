<div class="custom-variations-wrapper">
    <div class="custom-variations-list">
        @php
            $customVariations = old('custom_variations', $product?->custom_variations ?? []);
            if (is_string($customVariations)) {
                $customVariations = json_decode($customVariations, true) ?? [];
            }
        @endphp

        @if(!empty($customVariations))
            @foreach($customVariations as $index => $variation)
                <div class="custom-variation-item card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Variation Name') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="custom_variations[{{ $index }}][name]"
                                           class="form-control"
                                           value="{{ $variation['name'] ?? '' }}"
                                           placeholder="{{ __('e.g., King Size 72x84 inches') }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Price') }} <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="custom_variations[{{ $index }}][price]"
                                           class="form-control"
                                           value="{{ $variation['price'] ?? '' }}"
                                           placeholder="{{ __('e.g., 15000') }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-group mb-3 w-100">
                                    <button type="button" class="btn btn-danger w-100 remove-custom-variation">
                                        <i class="fa fa-trash"></i> {{ __('Remove') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label">{{ __('SKU') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <input type="text"
                                           name="custom_variations[{{ $index }}][sku]"
                                           class="form-control"
                                           value="{{ $variation['sku'] ?? '' }}"
                                           placeholder="{{ __('e.g., MAT-KING-72') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label">{{ __('Stock Quantity') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <input type="number"
                                           name="custom_variations[{{ $index }}][quantity]"
                                           class="form-control"
                                           value="{{ $variation['quantity'] ?? '' }}"
                                           placeholder="{{ __('e.g., 100') }}"
                                           min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <button type="button" class="btn btn-success add-custom-variation">
        <i class="fa fa-plus"></i> {{ __('Add Variation') }}
    </button>
</div>

<template id="custom-variation-template">
    <div class="custom-variation-item card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Variation Name') }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="custom_variations[__INDEX__][name]"
                               class="form-control"
                               placeholder="{{ __('e.g., King Size 72x84 inches') }}"
                               required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Price') }} <span class="text-danger">*</span></label>
                        <input type="number"
                               name="custom_variations[__INDEX__][price]"
                               class="form-control"
                               placeholder="{{ __('e.g., 15000') }}"
                               step="0.01"
                               min="0"
                               required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group mb-3 w-100">
                        <button type="button" class="btn btn-danger w-100 remove-custom-variation">
                            <i class="fa fa-trash"></i> {{ __('Remove') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('SKU') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                        <input type="text"
                               name="custom_variations[__INDEX__][sku]"
                               class="form-control"
                               placeholder="{{ __('e.g., MAT-KING-72') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('Stock Quantity') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                        <input type="number"
                               name="custom_variations[__INDEX__][quantity]"
                               class="form-control"
                               placeholder="{{ __('e.g., 100') }}"
                               min="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let variationIndex = {{ count($customVariations ?? []) }};

    document.querySelector('.add-custom-variation')?.addEventListener('click', function() {
        const template = document.getElementById('custom-variation-template');
        const html = template.innerHTML.replace(/__INDEX__/g, variationIndex);
        const list = document.querySelector('.custom-variations-list');
        list.insertAdjacentHTML('beforeend', html);
        variationIndex++;
    });

    document.querySelector('.custom-variations-wrapper')?.addEventListener('click', function(e) {
        if (e.target.closest('.remove-custom-variation')) {
            e.target.closest('.custom-variation-item').remove();
        }
    });
});
</script>
