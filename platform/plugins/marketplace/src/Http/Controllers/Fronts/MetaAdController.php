<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Http\Requests\Fronts\StoreMetaAdRequest;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Services\MetaAdsService;
use Botble\Marketplace\Tables\MetaAdTable;

class MetaAdController extends BaseController
{
    public function __construct(private MetaAdsService $metaAdsService) {}

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
            ->get(['id', 'name', 'campaign_id', 'meta_remote_id']);

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

        $data = $request->validated();

        $remoteId = null;
        if ($adSet->meta_remote_id) {
            $adAccount = $adSet->campaign?->adAccount;
            if ($adAccount?->is_connected) {
                $remoteId = $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->createAd($adAccount, $adSet->meta_remote_id, $data)
                );
            }
        }

        MetaAd::query()->create([
            'store_id' => $store->id,
            'ad_set_id' => $adSet->id,
            'campaign_id' => $adSet->campaign_id,
            'meta_remote_id' => $remoteId,
            'name' => $data['name'],
            'status' => $data['status'] ?? 'IN_REVIEW',
            'format' => $data['format'] ?? 'SINGLE_IMAGE',
            'primary_text' => $data['primary_text'] ?? null,
            'headline' => $data['headline'] ?? null,
            'description' => $data['description'] ?? null,
            'cta_button' => $data['cta_button'] ?? 'LEARN_MORE',
            'destination_url' => $data['destination_url'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'product_id' => $data['product_id'] ?? null,
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
            ->get(['id', 'name', 'campaign_id', 'meta_remote_id']);

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

        $data = $request->validated();

        if ($ad->meta_remote_id) {
            $adAccount = $adSet->campaign?->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->updateAd($adAccount, $ad->meta_remote_id, $data)
                );
            }
        }

        $ad->fill([
            'ad_set_id' => $adSet->id,
            'campaign_id' => $adSet->campaign_id,
            'name' => $data['name'],
            'status' => $data['status'] ?? $ad->status,
            'format' => $data['format'] ?? 'SINGLE_IMAGE',
            'primary_text' => $data['primary_text'] ?? null,
            'headline' => $data['headline'] ?? null,
            'description' => $data['description'] ?? null,
            'cta_button' => $data['cta_button'] ?? 'LEARN_MORE',
            'destination_url' => $data['destination_url'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'product_id' => $data['product_id'] ?? null,
        ])->save();

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

        if ($ad->meta_remote_id) {
            $adAccount = $ad->adSet?->campaign?->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->deleteAd($adAccount, $ad->meta_remote_id)
                );
            }
        }

        $ad->delete();

        return $this->httpResponse()
            ->setMessage(__('Ad deleted successfully.'));
    }
}
