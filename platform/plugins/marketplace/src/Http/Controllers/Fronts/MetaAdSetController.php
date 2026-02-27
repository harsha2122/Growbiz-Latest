<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Http\Requests\Fronts\StoreMetaAdSetRequest;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaAdsService;
use Botble\Marketplace\Tables\MetaAdSetTable;

class MetaAdSetController extends BaseController
{
    public function __construct(private MetaAdsService $metaAdsService) {}

    public function index(MetaAdSetTable $table)
    {
        $this->pageTitle(__('Meta Ad Sets'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(__('Create Ad Set'));

        $store = auth('customer')->user()->store;
        $campaigns = MetaCampaign::query()
            ->where('store_id', $store?->id)
            ->orderBy('name')
            ->get(['id', 'name', 'meta_remote_id']);

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ad-sets.create', compact('campaigns'));
    }

    public function store(StoreMetaAdSetRequest $request)
    {
        $store = auth('customer')->user()->store;

        $campaign = MetaCampaign::query()
            ->where('id', $request->input('campaign_id'))
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $data = $request->validated();

        $remoteId = null;
        if ($campaign->meta_remote_id) {
            $adAccount = $campaign->adAccount;
            if ($adAccount?->is_connected) {
                $remoteId = $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->createAdSet($adAccount, $campaign->meta_remote_id, $data)
                );
            }
        }

        MetaAdSet::query()->create([
            'store_id' => $store->id,
            'campaign_id' => $campaign->id,
            'meta_remote_id' => $remoteId,
            'name' => $data['name'],
            'status' => $data['status'] ?? 'PAUSED',
            'daily_budget' => $data['daily_budget'] ?? null,
            'targeting_age_min' => $data['targeting_age_min'] ?? 18,
            'targeting_age_max' => $data['targeting_age_max'] ?? 65,
            'targeting_genders' => $data['targeting_genders'] ?? 'all',
            'targeting_locations' => $data['targeting_locations'] ?? [],
            'targeting_interests' => $data['targeting_interests'] ?? [],
            'placements' => $data['placements'] ?? [],
            'optimization_goal' => $data['optimization_goal'] ?? 'LINK_CLICKS',
        ]);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.index'))
            ->setMessage(__('Ad set created successfully.'));
    }

    public function edit(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $adSet = MetaAdSet::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $this->pageTitle(__('Edit Ad Set: :name', ['name' => $adSet->name]));

        $campaigns = MetaCampaign::query()
            ->where('store_id', $store?->id)
            ->orderBy('name')
            ->get(['id', 'name', 'meta_remote_id']);

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ad-sets.edit', compact('adSet', 'campaigns'));
    }

    public function update(int|string $id, StoreMetaAdSetRequest $request)
    {
        $store = auth('customer')->user()->store;

        $adSet = MetaAdSet::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $data = $request->validated();

        if ($adSet->meta_remote_id) {
            $adAccount = $adSet->campaign?->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->updateAdSet($adAccount, $adSet->meta_remote_id, $data)
                );
            }
        }

        $adSet->fill([
            'campaign_id' => $data['campaign_id'],
            'name' => $data['name'],
            'status' => $data['status'] ?? $adSet->status,
            'daily_budget' => $data['daily_budget'] ?? null,
            'targeting_age_min' => $data['targeting_age_min'] ?? 18,
            'targeting_age_max' => $data['targeting_age_max'] ?? 65,
            'targeting_genders' => $data['targeting_genders'] ?? 'all',
            'targeting_locations' => $data['targeting_locations'] ?? [],
            'targeting_interests' => $data['targeting_interests'] ?? [],
            'placements' => $data['placements'] ?? [],
            'optimization_goal' => $data['optimization_goal'] ?? 'LINK_CLICKS',
        ])->save();

        return $this->httpResponse()
            ->setPreviousUrl(route('marketplace.vendor.meta-ads.ad-sets.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $adSet = MetaAdSet::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        if ($adSet->meta_remote_id) {
            $adAccount = $adSet->campaign?->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->deleteAdSet($adAccount, $adSet->meta_remote_id)
                );
            }
        }

        $adSet->delete();

        return $this->httpResponse()
            ->setMessage(__('Ad set deleted successfully.'));
    }
}
