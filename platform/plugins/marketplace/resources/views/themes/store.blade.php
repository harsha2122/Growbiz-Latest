@php
    $categories = ProductCategoryHelper::getProductCategoriesWithUrl();
    $categoriesRequest = (array) request()->input('categories', []);
    $categoryId = Arr::get($categoriesRequest, 0);
    $coverImage = $store->getMetaData('background', true);
@endphp

<div class="bb-shop-detail">
    <div class="container">
        @include(MarketplaceHelper::viewPath('includes.store-detail-banner'))

        @include(MarketplaceHelper::viewPath('includes.store-referral-program'))

        @include(MarketplaceHelper::viewPath('includes.store-service-chips'))

        @include(MarketplaceHelper::viewPath('includes.store-brands-slider'))

        @include(MarketplaceHelper::viewPath('includes.store-gallery'))

        @include(MarketplaceHelper::viewPath('includes.sponsored-video'))

        @include('plugins/marketplace::themes.includes.profile-reviews')

        @if (! $store->is_minimal_profile)
            <div class="row">
                <div class="col-xl-3 col-lg-4">
                    @include(MarketplaceHelper::viewPath('includes.store-filters'))
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="bb-product-items-wrapper">
                        @include(MarketplaceHelper::viewPath('stores.items'))
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
