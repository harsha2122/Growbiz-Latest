@php
    $customVariations = $product->custom_variations ?? [];
    if (is_string($customVariations)) {
        $customVariations = json_decode($customVariations, true) ?? [];
    }
@endphp

@if(!empty($customVariations))
    <div class="tp-product-custom-variations mb-20">
        <h3 class="tp-product-details-action-title">{{ __('Select Variation') }}</h3>
        <div class="custom-variation-options">
            @foreach($customVariations as $index => $variation)
                <div class="form-check custom-variation-option mb-2">
                    <input
                        class="form-check-input custom-variation-radio"
                        type="radio"
                        name="custom_variation"
                        id="custom_variation_{{ $index }}"
                        value="{{ $index }}"
                        data-variation-name="{{ $variation['name'] ?? '' }}"
                        data-variation-price="{{ $variation['price'] ?? 0 }}"
                        data-variation-sku="{{ $variation['sku'] ?? '' }}"
                        data-variation-quantity="{{ $variation['quantity'] ?? '' }}"
                    >
                    <label class="form-check-label custom-variation-label" for="custom_variation_{{ $index }}">
                        <span class="variation-name">{{ $variation['name'] ?? '' }}</span>
                        <span class="variation-price">{{ format_price($variation['price'] ?? 0) }}</span>
                    </label>
                </div>
            @endforeach
        </div>
        <input type="hidden" name="custom_variation_data" id="custom_variation_data" value="">
    </div>

    <style>
        .tp-product-custom-variations {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
        }
        .custom-variation-option {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
        }
        .custom-variation-option:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }
        .custom-variation-option:has(input:checked) {
            border-color: #007bff;
            background: #e8f0ff;
        }
        .custom-variation-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            cursor: pointer;
            margin-bottom: 0;
        }
        .variation-name {
            font-weight: 500;
            color: #333;
        }
        .variation-price {
            font-weight: 600;
            color: #007bff;
        }
        .form-check-input.custom-variation-radio {
            margin-right: 10px;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const variationRadios = document.querySelectorAll('.custom-variation-radio');
        const priceElement = document.querySelector('.tp-product-details-price.new-price, .tp-product-details-price-wrapper .new-price, [data-bb-value="product-price"]');
        const skuElement = document.querySelector('[data-bb-value="product-sku"]');
        const variationDataInput = document.getElementById('custom_variation_data');

        function updateProductDisplay(radio) {
            const price = parseFloat(radio.dataset.variationPrice) || 0;
            const sku = radio.dataset.variationSku || '';
            const name = radio.dataset.variationName || '';

            // Update price display
            if (priceElement) {
                priceElement.textContent = formatPrice(price);
            }

            // Update SKU display
            if (skuElement && sku) {
                skuElement.textContent = sku;
                skuElement.closest('.tp-product-details-query-item')?.style.setProperty('display', '');
            }

            // Store selected variation data for cart
            if (variationDataInput) {
                variationDataInput.value = JSON.stringify({
                    index: radio.value,
                    name: name,
                    price: price,
                    sku: sku
                });
            }
        }

        function formatPrice(price) {
            // Use the site's currency format
            return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                style: 'currency',
                currency: '{{ get_application_currency()->title }}',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(price);
        }

        variationRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                updateProductDisplay(this);
            });
        });
    });
    </script>
@endif
