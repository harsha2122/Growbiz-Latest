<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaCampaign;
use Illuminate\Http\Request;

class MetaAdSetController extends BaseController
{
    protected int $storeId;

    public function __construct()
    {
        abort_unless(MarketplaceHelper::isMetaAdsEnabled(), 403);
        abort_unless(auth('customer')->check(), 403);

        $this->storeId = auth('customer')->user()->store?->id ?? 0;
        abort_unless($this->storeId, 403);
    }

    public function create($campaignId)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($campaignId);

        $this->pageTitle(__('Create Ad Set — :campaign', ['campaign' => $campaign->name]));

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.create', compact('campaign'));
    }

    public function store(Request $request, $campaignId)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($campaignId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'daily_budget' => ['required', 'numeric', 'min:1'],
            'targeting_locations' => ['nullable', 'string'],
            'targeting_age_min' => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_age_max' => ['required', 'integer', 'min:13', 'max:65', 'gte:targeting_age_min'],
            'targeting_genders' => ['required', 'in:all,male,female'],
            'targeting_interests' => ['nullable', 'string'],
            'placements' => ['nullable', 'array'],
            'placements.*' => ['string', 'in:facebook_feed,instagram_feed,instagram_stories,instagram_reels,audience_network,messenger'],
            'optimization_goal' => ['required', 'in:LINK_CLICKS,IMPRESSIONS,CONVERSIONS,REACH'],
        ]);

        $validated['campaign_id'] = $campaign->id;
        $validated['store_id'] = $this->storeId;
        $validated['status'] = 'PAUSED';

        // Convert comma-separated strings to arrays
        $validated['targeting_locations'] = $validated['targeting_locations']
            ? array_map('trim', explode(',', $validated['targeting_locations']))
            : [];
        $validated['targeting_interests'] = $validated['targeting_interests']
            ? array_map('trim', explode(',', $validated['targeting_interests']))
            : [];

        MetaAdSet::query()->create($validated);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id))
            ->withCreatedSuccessMessage();
    }

    public function show($id)
    {
        $adSet = MetaAdSet::query()
            ->where('store_id', $this->storeId)
            ->with(['campaign', 'ads' => fn ($q) => $q->where('status', '!=', 'DELETED')])
            ->findOrFail($id);

        $this->pageTitle($adSet->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.show', compact('adSet'));
    }

    public function edit($id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($id);

        $this->pageTitle(__('Edit Ad Set: :name', ['name' => $adSet->name]));

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.edit', compact('adSet'));
    }

    public function update(Request $request, $id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'daily_budget' => ['required', 'numeric', 'min:1'],
            'targeting_locations' => ['nullable', 'string'],
            'targeting_age_min' => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_age_max' => ['required', 'integer', 'min:13', 'max:65', 'gte:targeting_age_min'],
            'targeting_genders' => ['required', 'in:all,male,female'],
            'targeting_interests' => ['nullable', 'string'],
            'placements' => ['nullable', 'array'],
            'placements.*' => ['string'],
            'optimization_goal' => ['required', 'in:LINK_CLICKS,IMPRESSIONS,CONVERSIONS,REACH'],
        ]);

        $validated['targeting_locations'] = $validated['targeting_locations']
            ? array_map('trim', explode(',', $validated['targeting_locations']))
            : [];
        $validated['targeting_interests'] = $validated['targeting_interests']
            ? array_map('trim', explode(',', $validated['targeting_interests']))
            : [];

        $adSet->update($validated);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id))
            ->withUpdatedSuccessMessage();
    }

    public function destroy($id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);
        $campaignId = $adSet->campaign_id;
        $adSet->update(['status' => 'DELETED']);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $campaignId))
            ->setMessage(__('Ad set deleted.'));
    }

    public function toggleStatus($id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);

        $newStatus = $adSet->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';
        $adSet->update(['status' => $newStatus]);

        return $this
            ->httpResponse()
            ->setMessage(__('Ad set status changed to :status.', ['status' => $newStatus]));
    }
}
