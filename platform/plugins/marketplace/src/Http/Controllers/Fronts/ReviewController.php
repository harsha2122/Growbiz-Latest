<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Tables\ReviewTable;

class ReviewController extends BaseController
{
    public function index(ReviewTable $table)
    {
        $this->pageTitle(__('Reviews'));

        $store = auth('customer')->user()->store;
        if ($store?->is_minimal_profile) {
            $reviews = $store->profileReviews()->with('customer:id,name')->latest()->paginate(20);
            $summary = $store->publicRatingSummary();

            return \Botble\Marketplace\Facades\MarketplaceHelper::view('vendor-dashboard.profile-reviews', compact('reviews', 'summary'));
        }

        Assets::addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        return $table->renderTable();
    }
}
