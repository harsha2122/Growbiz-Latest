<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Http\Requests\Fronts\StoreMetaAdRequest;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Tables\MetaAdTable;
use Botble\Ecommerce\Models\Product;

class MetaAdController extends BaseController
{
    public function index(MetaAdTable $table)
    {
        $this->pageTitle(__('Meta Ads'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(__('Create Ad'));

        $store = auth('customer')->user()->store;
        $adSets = MetaAdSet::query()
            ->where('store_id', $store?->id)
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_id']);

        $products = Product::query()
            ->where('store_id', $store?->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ads.create', compact('adSets', 'products'));
    }

    public function store(StoreMetaAdRequest $request)
    {
        $store = auth('customer')->user()->store;

        $adSet = MetaAdSet::query()
            ->where('id', $request->input('ad_set_id'))
            ->where('store_id', $store?->id)
            ->firstOrFail();

        MetaAd::query()->create([
            'store_id' => $store->id,
            'ad_set_id' => $adSet->id,
            'campaign_id' => $adSet->campaign_id,
            'name' => $request->input('name'),
            'status' => $request->input('status', 'IN_REVIEW'),
            'format' => $request->input('format', 'SINGLE_IMAGE'),
            'primary_text' => $request->input('primary_text'),
            'headline' => $request->input('headline'),
            'description' => $request->input('description'),
            'cta_button' => $request->input('cta_button', 'LEARN_MORE'),
            'destination_url' => $request->input('destination_url'),
            'image_url' => $request->input('image_url'),
            'product_id' => $request->input('product_id'),
        ]);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ads.index'))
            ->setMessage(__('Ad created successfully.'));
    }

    public function edit(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $ad = MetaAd::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $this->pageTitle(__('Edit Ad: :name', ['name' => $ad->name]));

        $adSets = MetaAdSet::query()
            ->where('store_id', $store?->id)
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_id']);

        $products = Product::query()
            ->where('store_id', $store?->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ads.edit', compact('ad', 'adSets', 'products'));
    }

    public function update(int|string $id, StoreMetaAdRequest $request)
    {
        $store = auth('customer')->user()->store;

        $ad = MetaAd::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $adSet = MetaAdSet::query()
            ->where('id', $request->input('ad_set_id'))
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $ad->fill([
            'ad_set_id' => $adSet->id,
            'campaign_id' => $adSet->campaign_id,
            'name' => $request->input('name'),
            'status' => $request->input('status', $ad->status),
            'format' => $request->input('format', 'SINGLE_IMAGE'),
            'primary_text' => $request->input('primary_text'),
            'headline' => $request->input('headline'),
            'description' => $request->input('description'),
            'cta_button' => $request->input('cta_button', 'LEARN_MORE'),
            'destination_url' => $request->input('destination_url'),
            'image_url' => $request->input('image_url'),
            'product_id' => $request->input('product_id'),
        ]);

        $ad->save();

        return $this->httpResponse()
            ->setPreviousUrl(route('marketplace.vendor.meta-ads.ads.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $ad = MetaAd::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $ad->delete();

        return $this->httpResponse()
            ->setMessage(__('Ad deleted successfully.'));
    }
}
