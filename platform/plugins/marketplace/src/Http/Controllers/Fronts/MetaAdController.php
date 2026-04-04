<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdSet;
use Illuminate\Http\Request;

class MetaAdController extends BaseController
{
    protected int $storeId;

    public function __construct()
    {
        if (! MarketplaceHelper::isMetaAdsEnabled()) { abort(404); }
        abort_unless(auth('customer')->check(), 403);

        $this->storeId = auth('customer')->user()->store?->id ?? 0;
        abort_unless($this->storeId, 403);
    }

    public function create($adSetId)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($adSetId);

        $this->pageTitle(__('Create Ad — :adset', ['adset' => $adSet->name]));

        $products = Product::query()
            ->where('store_id', $this->storeId)
            ->where('is_variation', 0)
            ->wherePublished()
            ->select('id', 'name', 'image', 'price')
            ->get();

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.create', compact('adSet', 'products'));
    }

    public function store(Request $request, $adSetId)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($adSetId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'format' => ['required', 'in:SINGLE_IMAGE,CAROUSEL,VIDEO'],
            'primary_text' => ['required', 'string', 'max:500'],
            'headline' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'cta_button' => ['required', 'in:LEARN_MORE,SHOP_NOW,SIGN_UP,BOOK_NOW,CONTACT_US,DOWNLOAD,GET_OFFER'],
            'destination_url' => ['required', 'url', 'max:500'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
        ]);

        $validated['ad_set_id'] = $adSet->id;
        $validated['campaign_id'] = $adSet->campaign_id;
        $validated['store_id'] = $this->storeId;
        $validated['status'] = 'IN_REVIEW';

        MetaAd::query()->create($validated);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id))
            ->withCreatedSuccessMessage();
    }

    public function show($id)
    {
        $ad = MetaAd::query()
            ->where('store_id', $this->storeId)
            ->with(['adSet.campaign', 'product'])
            ->findOrFail($id);

        $this->pageTitle($ad->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.show', compact('ad'));
    }

    public function edit($id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->with('adSet.campaign')->findOrFail($id);

        $this->pageTitle(__('Edit Ad: :name', ['name' => $ad->name]));

        $products = Product::query()
            ->where('store_id', $this->storeId)
            ->where('is_variation', 0)
            ->wherePublished()
            ->select('id', 'name', 'image', 'price')
            ->get();

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.edit', compact('ad', 'products'));
    }

    public function update(Request $request, $id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'format' => ['required', 'in:SINGLE_IMAGE,CAROUSEL,VIDEO'],
            'primary_text' => ['required', 'string', 'max:500'],
            'headline' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'cta_button' => ['required', 'in:LEARN_MORE,SHOP_NOW,SIGN_UP,BOOK_NOW,CONTACT_US,DOWNLOAD,GET_OFFER'],
            'destination_url' => ['required', 'url', 'max:500'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
        ]);

        $ad->update($validated);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id))
            ->withUpdatedSuccessMessage();
    }

    public function destroy($id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);
        $adSetId = $ad->ad_set_id;
        $ad->update(['status' => 'DELETED']);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $adSetId))
            ->setMessage(__('Ad deleted.'));
    }

    public function toggleStatus($id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);

        $newStatus = $ad->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';
        $ad->update(['status' => $newStatus]);

        return $this
            ->httpResponse()
            ->setMessage(__('Ad status changed to :status.', ['status' => $newStatus]));
    }

    public function preview($id)
    {
        $ad = MetaAd::query()
            ->where('store_id', $this->storeId)
            ->with(['adSet.campaign', 'product'])
            ->findOrFail($id);

        $storeName = auth('customer')->user()->store?->name ?? 'Your Store';

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.preview', compact('ad', 'storeName'));
    }
}
