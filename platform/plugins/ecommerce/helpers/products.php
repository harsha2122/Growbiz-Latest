<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Models\Review;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

if (! function_exists('get_product_by_id')) {
    function get_product_by_id(int|string $productId): ?Product
    {
        /**
         * @var ?Product $product
         */
        $product = Product::query()->find($productId);

        return $product;
    }
}

if (! function_exists('get_products')) {
    function get_products(array $params = [], array $filters = []): Collection|LengthAwarePaginator|Product|null
    {
        $params = [
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
            ],
            'with' => ['slugable'],
            'withCount' => [],
            'withAvg' => [],
            ...$params,
        ];

        $params = array_merge($params, EcommerceHelper::withReviewsParams());

        return app(ProductInterface::class)->getProducts($params, $filters);
    }
}

if (! function_exists('get_products_on_sale')) {
    function get_products_on_sale(array $params = []): Collection|LengthAwarePaginator|Product|null
    {
        $params = array_merge([
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
            ],
            'with' => [],
            'withCount' => [],
        ], $params + EcommerceHelper::withReviewsParams());

        return app(ProductInterface::class)->getOnSaleProducts($params);
    }
}

if (! function_exists('get_featured_products')) {
    function get_featured_products(array $params = []): Collection|LengthAwarePaginator|Product|null
    {
        $params = array_merge([
            'condition' => [
                'ec_products.is_featured' => 1,
                'ec_products.is_variation' => 0,
            ],
            'take' => null,
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'select' => ['ec_products.*'],
            'with' => [],
        ], $params + EcommerceHelper::withReviewsParams());

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_top_rated_products')) {
    function get_top_rated_products(int $limit = 10, array $with = [], array $withCount = []): Collection|LengthAwarePaginator|Product|null
    {
        if (! EcommerceHelper::isReviewEnabled()) {
            return collect();
        }

        $topProductIds = get_top_rated_product_ids($limit);

        return get_products(array_merge([
                'condition' => [
                    ['ec_products.id', 'IN', $topProductIds],
                    'ec_products.is_variation' => 0,
                ],
                'order_by' => [
                    'reviews_avg' => 'DESC',
                    'ec_products.order' => 'ASC',
                    'ec_products.created_at' => 'DESC',
                ],
                'take' => null,
                'paginate' => [
                    'per_page' => null,
                    'current_paged' => 1,
                ],
                'select' => [
                    'ec_products.*',
                ],
                'with' => $with,
                'withCount' => $withCount,
            ], EcommerceHelper::withReviewsParams()));
    }
}

if (! function_exists('get_top_rated_product_ids')) {
    function get_top_rated_product_ids(int $limit = 10): array
    {
        return Review::query()
            ->wherePublished()
            ->selectRaw('product_id, avg(star) AS star')
            ->groupBy('product_id')
            ->latest('star')
            ->limit($limit)
            ->pluck('product_id')
            ->all();
    }
}

if (! function_exists('get_trending_products')) {
    function get_trending_products(array $params = []): Collection|LengthAwarePaginator|Product|null
    {
        $params = array_merge([
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'take' => 10,
            'order_by' => [
                'ec_products.views' => 'DESC',
            ],
            'select' => ['ec_products.*'],
            'with' => [],
        ], $params + EcommerceHelper::withReviewsParams());

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_featured_product_categories')) {
    function get_featured_product_categories(): Collection|LengthAwarePaginator
    {
        return ProductCategory::query()
            ->where('is_featured', true)
            ->wherePublished()
            ->oldest('order')->latest()
            ->with('slugable')
            ->get();
    }
}

if (! function_exists('get_product_collections')) {
    function get_product_collections(
        array $condition = [],
        array $with = [],
        array $select = ['*']
    ): Collection {
        return ProductCollection::query()
            ->where($condition)
            ->wherePublished()
            ->select($select)
            ->with($with)
            ->get();
    }
}

if (! function_exists('get_products_by_collections')) {
    function get_products_by_collections(array $params = []): Collection
    {
        return app(ProductInterface::class)->getProductsByCollections($params);
    }
}

if (! function_exists('get_default_product_variation')) {
    function get_default_product_variation(int|string $configurableId): ?Product
    {
        return app(ProductInterface::class)
            ->getProductVariations($configurableId, [
                'condition' => [
                    'ec_products.status' => BaseStatusEnum::PUBLISHED,
                    'ec_products.is_variation' => 1,
                ],
                'take' => 1,
                'order_by' => [
                    'ec_product_variations.is_default' => 'DESC',
                ],
            ]);
    }
}

if (! function_exists('get_product_by_brand')) {
    function get_product_by_brand(array $params): Collection|LengthAwarePaginator|Product|null
    {
        return app(ProductInterface::class)->getProductByBrands($params);
    }
}

if (! function_exists('the_product_price')) {
    function the_product_price(Product $product, array $htmlWrap = []): string
    {
        $htmlWrapParams = array_merge([
            'open_wrap_price' => '<del>',
            'close_wrap_price' => '</del>',
            'open_wrap_sale' => '<ins>',
            'close_wrap_sale' => '</ins>',
        ], $htmlWrap);

        if ($product->front_sale_price !== $product->price) {
            return $htmlWrapParams['open_wrap_price'] . format_price($product->price) . $htmlWrapParams['close_wrap_price'] .
                $htmlWrapParams['open_wrap_sale'] . format_price($product->front_sale_price) . $htmlWrapParams['close_wrap_sale'];
        }

        return $htmlWrapParams['open_wrap_sale'] . $product->price . $htmlWrapParams['close_wrap_sale'];
    }
}

if (! function_exists('get_related_products')) {
    function get_related_products(Product $product, ?int $limit = null): Collection|LengthAwarePaginator|Product|null
    {
        if (! EcommerceHelper::isEnabledRelatedProducts()) {
            return new EloquentCollection();
        }

        $limit = $limit ?: theme_option('number_of_related_product', 4);

        $params = [
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => (int) $limit,
            'select' => [
                'ec_products.*',
            ],
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
        ];

        $params = array_merge($params, EcommerceHelper::withReviewsParams());

        $relatedIds = $product->products()->allRelatedIds()->toArray();

        $filters = [];

        if (! empty($relatedIds)) {
            $params['condition'][] = ['ec_products.id', 'IN', $relatedIds];
        } else {
            $params['condition'][] = ['ec_products.id', '!=', $product->getKey()];

            $relatedProductsSource = get_ecommerce_setting('related_products_source', 'category');

            if ($relatedProductsSource === 'brand' && $product->brand_id) {
                $filters = ['brands' => [$product->brand_id]];
            } else {
                $filters = ['categories' => $product->categories()->pluck('ec_product_categories.id')->all()];
            }
        }

        // Marketplace: never show other vendors' products as related to this one.
        if (is_plugin_active('marketplace') && $product->store_id) {
            $params['condition'][] = ['ec_products.store_id', '=', $product->store_id];
        }

        return app(ProductInterface::class)->filterProducts($filters, $params);
    }
}

if (! function_exists('get_instagram_video_url')) {
    /**
     * Fetch the public post page directly and pull the direct CDN video URL out of its
     * og:video meta tag, so it can play in a plain <video> element - this is the primary
     * embed method since it renders real, guaranteed-correct-looking playback, unlike
     * Instagram's public embed widget (build_instagram_embed_html() below), which
     * frequently degrades to just a "view profile" card without a Meta oEmbed API access
     * token (which needs a Developer App most site owners don't have).
     *
     * WARNING: this scrapes Instagram's page rather than using an official API. It's
     * against Instagram's Terms of Service, breaks whenever they change their page
     * markup, and repeated requests can get the server's IP rate-limited or blocked by
     * Instagram. Returns null on any failure or for non-video posts (caller should fall
     * back to the embed widget in that case).
     */
    function get_instagram_video_url(string $url): ?string
    {
        $cacheKey = 'instagram_scraped_video_' . md5($url);

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return $cached !== '' ? $cached : null;
        }

        $videoUrl = null;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(8)->get($url);

            if ($response->ok()) {
                $html = $response->body();

                if (preg_match('/<meta property="og:video:secure_url" content="([^"]+)"/', $html, $matches)
                    || preg_match('/<meta property="og:video" content="([^"]+)"/', $html, $matches)
                ) {
                    $videoUrl = html_entity_decode($matches[1]);
                } elseif (Str::contains((string) $response->effectiveUri(), 'login')) {
                    Log::warning('Instagram video scrape redirected to login for ' . $url);
                } else {
                    Log::warning('Instagram video scrape got 200 OK but no og:video tag for ' . $url);
                }
            } else {
                Log::warning('Instagram video scrape failed for ' . $url . ': HTTP ' . $response->status());
            }
        } catch (Exception $exception) {
            Log::warning('Instagram video scrape failed: ' . $exception->getMessage());
        }

        // Instagram's CDN video URLs are signed and expire after a while, so this can't be
        // cached long-lived - a short cache just avoids re-scraping on every page view.
        Cache::put($cacheKey, $videoUrl ?: '', $videoUrl ? 1800 : 600);

        return $videoUrl;
    }
}

if (! function_exists('build_instagram_embed_html')) {
    /**
     * Fallback for when get_instagram_video_url() can't find a direct video (e.g. an
     * image/carousel post, or Instagram changed their page markup): Instagram's own
     * public embed widget (embed.js) attempts to fetch and render the post client-side
     * from this blockquote. No Meta Developer App or API credentials required, but
     * without a real oEmbed API access token it can degrade to just a "view profile"
     * card rather than the full post - it's a best-effort fallback, not a guarantee.
     */
    function build_instagram_embed_html(string $url): string
    {
        return '<blockquote class="instagram-media" data-instgrm-permalink="' . e($url) . '" data-instgrm-version="14" style="background:#FFF; border:0; border-radius:3px; margin: 1px; max-width:540px; min-width:326px; padding:0; width:99%;"></blockquote>';
    }
}

if (! function_exists('get_cross_sale_products')) {
    function get_cross_sale_products(Product $product, ?int $limit = null, array $with = []): EloquentCollection
    {
        $with = array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $with);

        $reviewParams = EcommerceHelper::withReviewsParams();

        $limit = $limit ?: theme_option('number_of_cross_sale_product', 4);

        /**
         * @phpstan-ignore-next-line
         */
        return $product
            ->crossSales()
            ->limit((int) $limit)
            ->with($with)
            ->wherePublished()
            ->notOutOfStock()
            ->withCount($reviewParams['withCount'])
            ->withAvg($reviewParams['withAvg'][0], $reviewParams['withAvg'][1])
            ->get();
    }
}

if (! function_exists('get_up_sale_products')) {
    function get_up_sale_products(Product $product, int $limit = 4, array $with = []): EloquentCollection
    {
        $with = array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $with);

        /**
         * @phpstan-ignore-next-line
         */
        return $product
            ->upSales()
            ->limit($limit)
            ->with($with)
            ->wherePublished()
            ->notOutOfStock()
            ->withCount(EcommerceHelper::withReviewsParams()['withCount'])
            ->get();
    }
}

if (! function_exists('get_cart_cross_sale_products')) {
    function get_cart_cross_sale_products(array $productIds, int $limit = 4, array $with = []): Collection|LengthAwarePaginator|Product|null
    {
        $crossSaleIds = DB::table('ec_product_cross_sale_relations')
            ->whereIn('from_product_id', $productIds)
            ->pluck('to_product_id')
            ->all();

        $params = [
            'condition' => [
                ['ec_products.id', 'IN', $crossSaleIds],
                'ec_products.is_variation' => 0,
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => $limit,
            'select' => [
                'ec_products.*',
            ],
            'with' => array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $with),
        ];

        $params = array_merge($params, EcommerceHelper::withReviewsParams());

        return app(ProductInterface::class)->getProducts($params);
    }
}

if (! function_exists('get_product_attributes_with_set')) {
    function get_product_attributes_with_set(Product $product, int|string $setId): array
    {
        $productAttributes = app(ProductInterface::class)->getRelatedProductAttributes($product);

        $attributes = [];

        foreach ($productAttributes as $attribute) {
            if ($attribute->attribute_set_id === $setId) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}

if (! function_exists('handle_next_attributes_in_product')) {
    function handle_next_attributes_in_product(
        Collection $productAttributes,
        Collection $productVariationsInfo,
        int|string|null $setId,
        array $selectedAttributes,
        ?string $key,
        array $variationNextIds,
        ?Collection $variationInfo = null,
        array $unavailableAttributeIds = []
    ): array {
        foreach ($productAttributes as $attribute) {
            if ($variationInfo != null && $variationInfo->where('id', $attribute->id)->isEmpty()) {
                $unavailableAttributeIds[] = $attribute->id;
            }

            if (in_array($attribute->id, $selectedAttributes)) {
                $variationIds = $productVariationsInfo
                    ->where('attribute_set_id', $setId)
                    ->where('id', $attribute->id)
                    ->pluck('variation_id')
                    ->toArray();

                if ($key == 0) {
                    $variationNextIds = $variationIds;
                } else {
                    $variationNextIds = array_intersect($variationNextIds, $variationIds);
                }
            }
        }

        return [$variationNextIds, $unavailableAttributeIds];
    }
}
