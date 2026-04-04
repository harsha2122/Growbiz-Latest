<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaCampaign;
use Illuminate\Http\Request;

class MetaCampaignController extends BaseController
{
    protected int $storeId;

    public function __construct()
    {
        if (! MarketplaceHelper::isMetaAdsEnabled()) { abort(404); }
        abort_unless(auth('customer')->check(), 403);

        $this->storeId = auth('customer')->user()->store?->id ?? 0;
        abort_unless($this->storeId, 403);
    }

    public function index()
    {
        $this->pageTitle(__('Meta Ads — Campaigns'));

        $campaigns = MetaCampaign::query()
            ->where('store_id', $this->storeId)
            ->where('status', '!=', 'DELETED')
            ->withCount('adSets')
            ->latest()
            ->paginate(20);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $this->pageTitle(__('Create Campaign'));

        $adAccount = MetaAdAccount::query()
            ->where('store_id', $this->storeId)
            ->where('is_connected', true)
            ->first();

        if (! $adAccount) {
            return redirect()
                ->route('marketplace.vendor.meta-ads.connection')
                ->with('error', __('Please connect your Facebook account first.'));
        }

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'in:OUTCOME_TRAFFIC,OUTCOME_AWARENESS,OUTCOME_ENGAGEMENT,OUTCOME_SALES'],
            'daily_budget' => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $adAccount = MetaAdAccount::query()
            ->where('store_id', $this->storeId)
            ->where('is_connected', true)
            ->firstOrFail();

        $validated['store_id'] = $this->storeId;
        $validated['ad_account_id'] = $adAccount->id;
        $validated['status'] = 'PAUSED';

        MetaCampaign::query()->create($validated);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->withCreatedSuccessMessage();
    }

    public function show($id)
    {
        $campaign = MetaCampaign::query()
            ->where('store_id', $this->storeId)
            ->with(['adSets' => fn ($q) => $q->where('status', '!=', 'DELETED')->withCount('ads')])
            ->findOrFail($id);

        $this->pageTitle($campaign->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.show', compact('campaign'));
    }

    public function edit($id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        $this->pageTitle(__('Edit Campaign: :name', ['name' => $campaign->name]));

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'in:OUTCOME_TRAFFIC,OUTCOME_AWARENESS,OUTCOME_ENGAGEMENT,OUTCOME_SALES'],
            'daily_budget' => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $campaign->update($validated);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy($id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);
        $campaign->update(['status' => 'DELETED']);

        return $this
            ->httpResponse()
            ->setMessage(__('Campaign deleted.'));
    }

    public function toggleStatus($id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        $newStatus = $campaign->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';
        $campaign->update(['status' => $newStatus]);

        return $this
            ->httpResponse()
            ->setMessage(__('Campaign status changed to :status.', ['status' => $newStatus]));
    }
}
