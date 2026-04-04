<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaCampaign;
use Illuminate\Http\Request;

class MetaCampaignController extends BaseController
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

    public function index()
    {
        $this->pageTitle('Campaigns');

        $campaigns = MetaCampaign::query()
            ->where('store_id', $this->storeId)
            ->withCount('adSets')
            ->latest()
            ->paginate(20);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $this->pageTitle('Create Campaign');

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'objective'        => ['required', 'in:OUTCOME_TRAFFIC,OUTCOME_AWARENESS,OUTCOME_ENGAGEMENT,OUTCOME_SALES'],
            'daily_budget'     => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget'  => ['nullable', 'numeric', 'min:1'],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $validated['store_id'] = $this->storeId;
        $validated['status'] = 'PAUSED';

        MetaCampaign::query()->create($validated);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->withCreatedSuccessMessage();
    }

    public function show(int $id)
    {
        $campaign = MetaCampaign::query()
            ->where('store_id', $this->storeId)
            ->with(['adSets' => fn ($q) => $q->withCount('ads')])
            ->findOrFail($id);

        $this->pageTitle($campaign->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.show', compact('campaign'));
    }

    public function edit(int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        $this->pageTitle('Edit: ' . $campaign->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'objective'       => ['required', 'in:OUTCOME_TRAFFIC,OUTCOME_AWARENESS,OUTCOME_ENGAGEMENT,OUTCOME_SALES'],
            'daily_budget'    => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $campaign->update($validated);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);
        $campaign->delete();

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->setMessage('Campaign deleted.');
    }

    public function toggleStatus(int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);
        $campaign->update(['status' => $campaign->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE']);

        return $this->httpResponse()->setMessage('Campaign status updated.');
    }
}
