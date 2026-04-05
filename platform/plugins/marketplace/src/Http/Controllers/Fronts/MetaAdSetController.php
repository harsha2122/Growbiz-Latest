<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $validated['store_id']    = $this->storeId;
        $validated['status']      = 'PAUSED';

        $validated['targeting_locations'] = $validated['targeting_locations']
            ? array_map('trim', explode(',', $validated['targeting_locations'])) : [];
        $validated['targeting_interests'] = $validated['targeting_interests']
            ? array_map('trim', explode(',', $validated['targeting_interests'])) : [];

        $adSet = MetaAdSet::query()->create($validated);

        if ($campaign->meta_campaign_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    $metaClient = app(MetaApiClient::class);
                    $targeting  = $metaClient->buildTargeting([
                        'targeting_age_min'   => $adSet->targeting_age_min,
                        'targeting_age_max'   => $adSet->targeting_age_max,
                        'targeting_genders'   => $adSet->targeting_genders,
                        'targeting_locations' => $adSet->targeting_locations,
                    ]);

                    $result = $metaClient->createAdSet($adAccount->access_token, $adAccount->ad_account_id, [
                        'name'              => $adSet->name,
                        'campaign_id'       => $campaign->meta_campaign_id,
                        'daily_budget'      => (int) ($adSet->daily_budget * 100),
                        'billing_event'     => 'IMPRESSIONS',
                        'optimization_goal' => $adSet->optimization_goal,
                        'targeting'         => $targeting,
                        'status'            => 'PAUSED',
                    ]);

                    if (! empty($result['id'])) {
                        $adSet->update(['meta_adset_id' => $result['id']]);
                    } elseif (! empty($result['error'])) {
                        Log::warning('Meta ad set create API error', ['error' => $result['error']]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Meta ad set push failed', ['error' => $e->getMessage()]);
                }
            }
        }

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

        if ($adSet->meta_adset_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    $metaClient = app(MetaApiClient::class);
                    $targeting  = $metaClient->buildTargeting([
                        'targeting_age_min'   => $adSet->targeting_age_min,
                        'targeting_age_max'   => $adSet->targeting_age_max,
                        'targeting_genders'   => $adSet->targeting_genders,
                        'targeting_locations' => $adSet->targeting_locations,
                    ]);

                    $metaClient->updateAdSet($adAccount->access_token, $adSet->meta_adset_id, [
                        'name'         => $adSet->name,
                        'daily_budget' => (int) ($adSet->daily_budget * 100),
                        'targeting'    => $targeting,
                        'status'       => $adSet->status,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Meta ad set update API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int $id)
    {
        $adSet      = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);
        $campaignId = $adSet->campaign_id;

        if ($adSet->meta_adset_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->deleteAdSet($adAccount->access_token, $adSet->meta_adset_id);
                } catch (\Throwable $e) {
                    Log::error('Meta ad set delete API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $adSet->delete();

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $campaignId))
            ->setMessage('Ad set deleted.');
    }

    public function toggleStatus(int $id)
    {
        $adSet     = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($id);
        $newStatus = $adSet->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';
        $adSet->update(['status' => $newStatus]);

        if ($adSet->meta_adset_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->updateAdSet($adAccount->access_token, $adSet->meta_adset_id, [
                        'status' => $newStatus,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Meta ad set toggleStatus API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $this->httpResponse()->setMessage('Ad set status updated.');
    }

    private function getConnectedAccount(): ?MetaAdAccount
    {
        return MetaAdAccount::query()
            ->where('store_id', $this->storeId)
            ->where('is_connected', true)
            ->whereNotNull('access_token')
            ->whereNotNull('ad_account_id')
            ->first();
    }
}
