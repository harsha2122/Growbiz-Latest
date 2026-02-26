<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Http\Requests\Fronts\StoreMetaAdSetRequest;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Tables\MetaAdSetTable;

class MetaAdSetController extends BaseController
{
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
            ->get(['id', 'name']);

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ad-sets.create', compact('campaigns'));
    }

    public function store(StoreMetaAdSetRequest $request)
    {
        $store = auth('customer')->user()->store;

        $campaign = MetaCampaign::query()
            ->where('id', $request->input('campaign_id'))
            ->where('store_id', $store?->id)
            ->firstOrFail();

        MetaAdSet::query()->create([
            'store_id' => $store->id,
            'campaign_id' => $campaign->id,
            'name' => $request->input('name'),
            'status' => $request->input('status', 'PAUSED'),
            'daily_budget' => $request->input('daily_budget'),
            'targeting_age_min' => $request->input('targeting_age_min', 18),
            'targeting_age_max' => $request->input('targeting_age_max', 65),
            'targeting_genders' => $request->input('targeting_genders', 'all'),
            'targeting_locations' => $request->input('targeting_locations', []),
            'targeting_interests' => $request->input('targeting_interests', []),
            'placements' => $request->input('placements', []),
            'optimization_goal' => $request->input('optimization_goal', 'LINK_CLICKS'),
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
            ->get(['id', 'name']);

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.ad-sets.edit', compact('adSet', 'campaigns'));
    }

    public function update(int|string $id, StoreMetaAdSetRequest $request)
    {
        $store = auth('customer')->user()->store;

        $adSet = MetaAdSet::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $adSet->fill([
            'campaign_id' => $request->input('campaign_id'),
            'name' => $request->input('name'),
            'status' => $request->input('status', $adSet->status),
            'daily_budget' => $request->input('daily_budget'),
            'targeting_age_min' => $request->input('targeting_age_min', 18),
            'targeting_age_max' => $request->input('targeting_age_max', 65),
            'targeting_genders' => $request->input('targeting_genders', 'all'),
            'targeting_locations' => $request->input('targeting_locations', []),
            'targeting_interests' => $request->input('targeting_interests', []),
            'placements' => $request->input('placements', []),
            'optimization_goal' => $request->input('optimization_goal', 'LINK_CLICKS'),
        ]);

        $adSet->save();

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

        $adSet->delete();

        return $this->httpResponse()
            ->setMessage(__('Ad set deleted successfully.'));
    }
}
