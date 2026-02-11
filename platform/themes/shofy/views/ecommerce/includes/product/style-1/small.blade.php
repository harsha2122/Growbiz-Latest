@if ($product instanceof \Botble\Ecommerce\Models\Product && $product->exists)
    <div class="tp-product-sm-item d-flex align-items-center">
        <div class="tp-product-thumb mr-25 fix">
            <a href="{{ $product->url }}">
                {{ RvMedia::image($product->image, $product->name, 'thumb') }}
            </a>
        </div>
        <div class="tp-product-sm-content">
            @if (is_plugin_active('marketplace') && $product->store?->id)
                <div class="tp-product-category tp-store-name">
                    <svg class="tp-store-icon" style="display:inline-block;width:14px;height:14px;vertical-align:middle;margin-right:3px;fill:none;stroke:currentColor" width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <a href="{{ $product->store->url }}">{{ $product->store->name }}</a> {!! $product->store->badge !!}
                </div>
            @endif

            <h3 class="tp-product-title">
                <a href="{{ $product->url }}">{{ $product->name }}</a>
            </h3>

                <div @class(['tp-product-price-review' => theme_option('product_listing_review_style', 'default') !== 'default' && EcommerceHelper::isReviewEnabled() && ($product->reviews_avg || theme_option('ecommerce_hide_rating_star_when_is_zero', 'no') === 'no')])>
                @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.rating'))

                @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.price'))
            </div>
        </div>
    </div>
@endif
