@if (! EcommerceHelper::hideProductPrice() || EcommerceHelper::isCartEnabled())
    @once
        <style>
            .bb-product-price-prefix {
                display: block;
                font-size: .75rem;
                font-weight: 400;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: .02em;
                margin-bottom: 2px;
            }
        </style>
    @endonce

    @php
        $isDisplayPriceOriginal ??= true;
        $priceWrapperClassName ??= null;
        $priceClassName ??= null;
        $priceOriginalClassName ??= null;
        $priceOriginalWrapperClassName ??= null;
    @endphp

    <div class="{{ $priceWrapperClassName === null ? 'bb-product-price mb-3' : $priceWrapperClassName }}">
        @if ((string) $product->product_type === 'service')
            <span class="bb-product-price-prefix">{{ __('Starting from') }}</span>
        @endif

        <span
            class="{{ $priceClassName === null ? 'bb-product-price-text fw-bold' : $priceClassName }}"
            data-bb-value="product-price"
        >{{ $priceFormatted ?? $product->price()->displayAsText() }}</span>

        @if ($isDisplayPriceOriginal && $product->isOnSale())
            @include(EcommerceHelper::viewPath('includes.product-prices.original'), [
                'priceWrapperClassName' => $priceOriginalWrapperClassName,
                'priceClassName' => $priceOriginalClassName,
            ])
        @endif
    </div>
@endif
