<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaCampaign;
use Illuminate\Http\Request;

class MetaAdSetController extends BaseController
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

    public function create(int $campaignId)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($campaignId);

        $this->pageTitle('Create Ad Set');

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.create', compact('campaign'));
    }

    public function store(Request $request, int $campaignId)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($campaignId);

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'daily_budget'        => ['required', 'numeric', 'min:1'],
            'targeting_age_min'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_age_max'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_genders'   => ['required', 'in:all,male,female'],
            'optimization_goal'   => ['required', 'in:LINK_CLICKS,IMPRESSIONS,REACH'],
            'targeting_locations' => ['nullable', 'string'],
            'targeting_interests' => ['nullable', 'string'],
            'placements'          => ['nullable', 'array'],
        ]);

        $validated['campaign_id'] = $campaign->id;
        $validated['store_id'] = $this->storeId;
        $validated['status'] = 'PAUSED';
        $validated['targeting_locations'] = $validated['targeting_locations']
            ? array_map('trim', explode(',', $validated['targeting_locations'])) : [];
        $validated['targeting_interests'] = $validated['targeting_interests']
            ? array_map('trim', explode(',', $validated['targeting_interests'])) : [];

        MetaAdSet::query()->create($validated);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id))
            ->withCreatedSuccessMessage();
    }

    public function show(int $id)
    {
        $adSet = MetaAdSet::query()
            ->where('store_id', $this->storeId)
            ->with(['campaign', 'ads'])
            ->findOrFail($id);

        $this->pageTitle($adSet->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.show', compact('adSet'));
    }

    public function edit(int $id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($id);

        $this->pageTitle('Edit: ' . $adSet->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.edit', compact('adSet'));
    }

    public function update(Request $request, int $id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'daily_budget'        => ['required', 'numeric', 'min:1'],
            'targeting_age_min'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_age_max'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_genders'   => ['required', 'in:all,male,female'],
            'optimization_goal'   => ['required', 'in:LINK_CLICKS,IMPRESSIONS,REACH'],
            'targeting_locations' => ['nullable', 'string'],
            'targeting_interests' => ['nullable', 'string'],
            'placements'          => ['nullable', 'array'],
        ]);

        $validated['targeting_locations'] = $validated['targeting_locations']
            ? array_map('trim', explode(',', $validated['targeting_locations'])) : [];
        $validated['targeting_interests'] = $validated['targeting_interests']
            ? array_map('trim', explode(',', $validated['targeting_interests'])) : [];

        $adSet->update($validated);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int $id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);
        $campaignId = $adSet->campaign_id;
        $adSet->delete();

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $campaignId))
            ->setMessage('Ad set deleted.');
    }

    public function toggleStatus(int $id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);
        $adSet->update(['status' => $adSet->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE']);

        return $this->httpResponse()->setMessage('Ad set status updated.');
    }
}
