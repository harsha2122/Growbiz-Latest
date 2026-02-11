<div class="tp-product-badge-3">
    @if ($product->isOutOfStock())
        <span class="product-out-stock">{{ __('Out Of Stock') }}</span>
    @else
        @if ($product->productLabels->isNotEmpty())
            @foreach ($product->productLabels as $label)
                <span {!! $label->css_styles !!}>{{ $label->name }}</span>
            @endforeach
        @else
            @if ($product->front_sale_price !== $product->price)
                <span class="product-sale">{{ get_sale_percentage($product->price, $product->front_sale_price) }}</span>
            @endif
        @endif
    @endif
    @if ($product->flashSales()->notExpired()->exists())
        <span class="product-flash-sale-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg> {{ __('Flash Sale') }}</span>
    @endif
</div>
