<div @class(['tp-product-item-2 mb-40', $class ?? null])>
    <div class="tp-product-thumb-2 p-relative z-index-1 fix w-img">
        <a href="{{ $product->url }}">
            {{ RvMedia::image($product->image, $product->name, $style === 2 ? 'thumb' : 'medium', true) }}
        </a>

        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.badges'))

        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-2.actions'))
    </div>
    <div class="tp-product-content-2 pt-15">
        {!! apply_filters('ecommerce_before_product_item_content_renderer', null, $product) !!}

        @if (is_plugin_active('marketplace') && $product->store?->id)
            <div class="tp-product-tag-2">
                <svg class="tp-store-icon" style="display:inline-block;width:14px;height:14px;vertical-align:middle;margin-right:3px;fill:none;stroke:currentColor" width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <a href="{{ $product->store->url }}">{{ $product->store->name }}</a> {!! $product->store->badge !!}
            </div>
        @endif

        <h3 @class(['tp-product-title-2', theme_option('ecommerce_product_name_lines', '2') === '1' ? 'line-clamp-1' : 'line-clamp-2'])>
            <a href="{{ $product->url }}" title="{{ $product->name }}">{{ $product->name }}</a>
        </h3>

        <div @class(['mt-2 tp-product-price-review' => theme_option('product_listing_review_style', 'default') !== 'default' && EcommerceHelper::isReviewEnabled() && ($product->reviews_avg || theme_option('ecommerce_hide_rating_star_when_is_zero', 'no') === 'no')])>
            @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-2.rating'))

            @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-2.price'))
        </div>

        {!! apply_filters('ecommerce_after_product_item_content_renderer', null, $product) !!}
    </div>
</div>
