@php
    $customVariations = $product->custom_variations ?? [];
    if (is_string($customVariations)) {
        $customVariations = json_decode($customVariations, true) ?? [];
    }
@endphp

@if(!empty($customVariations))
    <div class="tp-product-custom-variations mb-20">
        <h3 class="tp-product-details-action-title">{{ __('Select Variation') }}</h3>
        <div class="custom-variation-grid">
            @foreach($customVariations as $index => $variation)
                <div class="custom-variation-card {{ $index === 0 ? 'selected' : '' }}" data-index="{{ $index }}">
                    <input
                        class="custom-variation-radio visually-hidden"
                        type="radio"
                        name="custom_variation"
                        id="custom_variation_{{ $index }}"
                        value="{{ $index }}"
                        data-variation-name="{{ $variation['name'] ?? '' }}"
                        data-variation-price="{{ $variation['price'] ?? 0 }}"
                        data-variation-sku="{{ $variation['sku'] ?? '' }}"
                        data-variation-quantity="{{ $variation['quantity'] ?? '' }}"
                        {{ $index === 0 ? 'checked' : '' }}
                    >
                    <label class="custom-variation-card-label" for="custom_variation_{{ $index }}">
                        <span class="variation-check">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
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
            margin-bottom: 20px;
        }
        .tp-product-custom-variations .tp-product-details-action-title {
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        .custom-variation-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        @media (max-width: 480px) {
            .custom-variation-grid {
                grid-template-columns: 1fr;
            }
        }
        .custom-variation-card {
            position: relative;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .custom-variation-card:hover {
            border-color: #0989ff;
            background: #f8fbff;
        }
        .custom-variation-card.selected {
            border-color: #0989ff;
            background: #f0f7ff;
        }
        .custom-variation-card-label {
            display: flex;
            flex-direction: column;
            padding: 12px 14px;
            cursor: pointer;
            margin: 0;
            min-height: 70px;
            justify-content: center;
        }
        .variation-check {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e5e5e5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all 0.2s ease;
        }
        .variation-check svg {
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s ease;
        }
        .custom-variation-card.selected .variation-check {
            background: #0989ff;
        }
        .custom-variation-card.selected .variation-check svg {
            opacity: 1;
            transform: scale(1);
        }
        .variation-name {
            font-size: 13px;
            font-weight: 500;
            color: #333;
            line-height: 1.4;
            padding-right: 25px;
            margin-bottom: 4px;
        }
        .variation-price {
            font-size: 15px;
            font-weight: 700;
            color: #0989ff;
        }
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const variationCards = document.querySelectorAll('.custom-variation-card');
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

            // Update card selection visual
            variationCards.forEach(card => card.classList.remove('selected'));
            radio.closest('.custom-variation-card')?.classList.add('selected');
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                style: 'currency',
                currency: '{{ get_application_currency()->title }}',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(price);
        }

        // Handle card click
        variationCards.forEach(function(card) {
            card.addEventListener('click', function(e) {
                const radio = this.querySelector('.custom-variation-radio');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    updateProductDisplay(radio);
                }
            });
        });

        // Handle radio change
        variationRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                updateProductDisplay(this);
            });

            // Initialize first selected variation
            if (radio.checked) {
                updateProductDisplay(radio);
            }
        });
    });
    </script>
@endif
