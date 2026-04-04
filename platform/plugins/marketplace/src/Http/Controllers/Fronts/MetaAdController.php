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
    protected int $storeId = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! MarketplaceHelper::isMetaAdsEnabled()) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'Meta Ads is not enabled.');
            }
            $this->storeId = auth('customer')->user()?->store?->id ?? 0;
            if (! $this->storeId) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'No store found for your account.');
            }
            return $next($request);
        });
    }

    public function create(int $adSetId)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($adSetId);

        $this->pageTitle('Create Ad');

        $products = Product::query()
            ->where('store_id', $this->storeId)
            ->where('is_variation', 0)
            ->wherePublished()
            ->select('id', 'name', 'image', 'price')
            ->get();

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.create', compact('adSet', 'products'));
    }

    public function store(Request $request, int $adSetId)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($adSetId);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'format'          => ['required', 'in:SINGLE_IMAGE,CAROUSEL,VIDEO'],
            'primary_text'    => ['required', 'string', 'max:500'],
            'headline'        => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'cta_button'      => ['required', 'in:LEARN_MORE,SHOP_NOW,SIGN_UP,BOOK_NOW,CONTACT_US,DOWNLOAD,GET_OFFER'],
            'destination_url' => ['required', 'url', 'max:500'],
            'image_url'       => ['nullable', 'string', 'max:500'],
            'product_id'      => ['nullable', 'integer'],
        ]);

        $validated['ad_set_id'] = $adSet->id;
        $validated['campaign_id'] = $adSet->campaign_id;
        $validated['store_id'] = $this->storeId;
        $validated['status'] = 'IN_REVIEW';

        MetaAd::query()->create($validated);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id))
            ->withCreatedSuccessMessage();
    }

    public function show(int $id)
    {
        $ad = MetaAd::query()
            ->where('store_id', $this->storeId)
            ->with(['adSet.campaign', 'product'])
            ->findOrFail($id);

        $this->pageTitle($ad->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.show', compact('ad'));
    }

    public function edit(int $id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->with('adSet')->findOrFail($id);

        $this->pageTitle('Edit: ' . $ad->name);

        $products = Product::query()
            ->where('store_id', $this->storeId)
            ->where('is_variation', 0)
            ->wherePublished()
            ->select('id', 'name', 'image', 'price')
            ->get();

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.edit', compact('ad', 'products'));
    }

    public function update(Request $request, int $id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'format'          => ['required', 'in:SINGLE_IMAGE,CAROUSEL,VIDEO'],
            'primary_text'    => ['required', 'string', 'max:500'],
            'headline'        => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'cta_button'      => ['required', 'in:LEARN_MORE,SHOP_NOW,SIGN_UP,BOOK_NOW,CONTACT_US,DOWNLOAD,GET_OFFER'],
            'destination_url' => ['required', 'url', 'max:500'],
            'image_url'       => ['nullable', 'string', 'max:500'],
            'product_id'      => ['nullable', 'integer'],
        ]);

        $ad->update($validated);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int $id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);
        $adSetId = $ad->ad_set_id;
        $ad->delete();

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $adSetId))
            ->setMessage('Ad deleted.');
    }

    public function toggleStatus(int $id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);
        $ad->update(['status' => $ad->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE']);

        return $this->httpResponse()->setMessage('Ad status updated.');
    }

    public function preview(int $id)
    {
        $ad = MetaAd::query()
            ->where('store_id', $this->storeId)
            ->with(['adSet.campaign', 'product'])
            ->findOrFail($id);

        $storeName = auth('customer')->user()->store?->name ?? 'Your Store';

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.preview', compact('ad', 'storeName'));
    }
}
