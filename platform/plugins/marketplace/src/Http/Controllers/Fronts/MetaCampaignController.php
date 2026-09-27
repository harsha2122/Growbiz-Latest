<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Controllers\Fronts\Concerns\HandlesMetaApiResults;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaCampaignController extends BaseController
{
    use HandlesMetaApiResults;

    protected int $storeId = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! MarketplaceHelper::isMetaAdsEnabled()) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'Meta Ads is not enabled.');
            }
            $store = auth('customer')->user()?->store;
            $this->storeId = $store?->id ?? 0;
            if (! $this->storeId) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'No store found for your account.');
            }
            if (! $store->hasMetaAdsAccess()) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'Your current subscription plan does not include Meta Ads. Please contact admin to upgrade your plan.');
            }

            return $next($request);
        });

        Assets::addScriptsDirectly(['vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.min.js'])
            ->addStylesDirectly(['vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.css']);
    }

    public function index()
    {
        $this->pageTitle('Campaigns');

        $campaigns = MetaCampaign::query()
            ->where('store_id', $this->storeId)
            ->withCount('adSets')
            ->latest()
            ->paginate(20);

        // Live check: payment method
        $hasPaymentMethod = null;
        $accountStatus    = null;
        $adAccount        = $this->getConnectedAccount();

        if ($adAccount) {
            if ($adAccount->account_status !== null) {
                $accountStatus    = (int) $adAccount->account_status;
                $hasPaymentMethod = (bool) $adAccount->has_payment_method;
            } else {
                $details = app(MetaApiClient::class)
                    ->getAdAccountDetails($adAccount->access_token, $adAccount->ad_account_id);

                if (! empty($details['account_status'])) {
                    $accountStatus    = (int) $details['account_status'];
                    $hasPaymentMethod = ! empty($details['funding_source_details']);
                }
            }
        }

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.index', compact(
            'campaigns', 'hasPaymentMethod', 'accountStatus'
        ));
    }

    public function create()
    {
        $this->pageTitle('Create Campaign');

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'objective'       => ['required', 'in:OUTCOME_TRAFFIC,OUTCOME_AWARENESS,OUTCOME_ENGAGEMENT,OUTCOME_SALES,OUTCOME_LEADS,OUTCOME_APP_PROMOTION'],
            'daily_budget'    => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $validated['store_id'] = $this->storeId;
        $validated['status']        = 'PAUSED';
        $validated['ad_account_id'] = 0;

        $campaign = MetaCampaign::query()->create($validated);

        $response = $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id));

        $adAccount = $this->getConnectedAccount();
        if (! $adAccount) {
            return $response->setMessage('Campaign saved as a draft. Connect your Facebook account, then use "Push to Meta" to publish it.');
        }

        $result = $this->syncCampaignToMeta($campaign, $adAccount);

        if (! $result['success']) {
            return $response->setError()->setMessage('Campaign saved, but it could not be created on Meta: ' . $result['error'] . ' Fix the issue and use "Push to Meta" to retry.');
        }

        return $response->withCreatedSuccessMessage();
    }

    public function show(int $id)
    {
        $campaign = MetaCampaign::query()
            ->where('store_id', $this->storeId)
            ->with(['adSets' => fn ($q) => $q->withCount('ads')])
            ->findOrFail($id);

        $this->pageTitle($campaign->name);

        $dailySeries = $campaign->dailyInsights()->orderBy('date')->get();

        $chartData = [
            'dates'       => $dailySeries->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->values(),
            'spend'       => $dailySeries->pluck('spend')->map(fn ($v) => (float) $v)->values(),
            'impressions' => $dailySeries->pluck('impressions')->map(fn ($v) => (int) $v)->values(),
            'clicks'      => $dailySeries->pluck('clicks')->map(fn ($v) => (int) $v)->values(),
        ];

        // Age/gender breakdown -> grouped bar chart series (one series per gender, categories = age ranges)
        $ageGenderChart = ['categories' => [], 'series' => []];
        $ageGenderRows = collect($campaign->age_gender_breakdown ?? []);
        if ($ageGenderRows->isNotEmpty()) {
            $ages = $ageGenderRows->pluck('age')->unique()->sort()->values();
            $genders = $ageGenderRows->pluck('gender')->unique()->values();
            $ageGenderChart['categories'] = $ages->all();
            foreach ($genders as $gender) {
                $ageGenderChart['series'][] = [
                    'name' => ucfirst($gender),
                    'data' => $ages->map(function ($age) use ($ageGenderRows, $gender) {
                        $row = $ageGenderRows->first(fn ($r) => ($r['age'] ?? null) === $age && ($r['gender'] ?? null) === $gender);
                        return (int) ($row['impressions'] ?? 0);
                    })->all(),
                ];
            }
        }

        // Placement (publisher_platform) breakdown -> donut chart
        $placementRows = collect($campaign->placement_breakdown ?? []);
        $placementChart = [
            'labels' => $placementRows->pluck('publisher_platform')->map(fn ($p) => ucfirst($p ?? 'unknown'))->values(),
            'series' => $placementRows->pluck('impressions')->map(fn ($v) => (int) $v)->values(),
        ];

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.campaigns.show', compact(
            'campaign', 'chartData', 'ageGenderChart', 'placementChart'
        ));
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
            'objective'       => ['required', 'in:OUTCOME_TRAFFIC,OUTCOME_AWARENESS,OUTCOME_ENGAGEMENT,OUTCOME_SALES,OUTCOME_LEADS,OUTCOME_APP_PROMOTION'],
            'daily_budget'    => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // Objective can't be changed on Meta once a campaign exists there.
        if ($campaign->meta_campaign_id && $validated['objective'] !== $campaign->objective) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('The objective of a campaign already published to Meta cannot be changed. Create a new campaign instead.');
        }

        $campaign->update($validated);

        $response = $this->httpResponse()->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'));

        if ($campaign->meta_campaign_id && ($adAccount = $this->getConnectedAccount())) {
            $result = app(MetaApiClient::class)->updateCampaign($adAccount->access_token, $campaign->meta_campaign_id, [
                'name' => $campaign->name,
            ]);

            if ($error = $this->metaError($result, $adAccount, 'campaign update')) {
                return $response->setError()->setMessage('Saved here, but Meta was not updated: ' . $error);
            }
        }

        return $response->withUpdatedSuccessMessage();
    }

    public function destroy(int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        if ($campaign->meta_campaign_id) {
            // Never drop our record while the campaign may still be spending on Meta -
            // the vendor would lose the only place to stop it.
            $adAccount = $this->getConnectedAccount();
            if (! $adAccount) {
                return $this->httpResponse()
                    ->setError()
                    ->setMessage('This campaign exists on Meta. Reconnect your Facebook account so it can be deleted there first.');
            }

            $result = app(MetaApiClient::class)->deleteCampaign($adAccount->access_token, $campaign->meta_campaign_id);

            if (! MetaApiClient::isDeleted($result)) {
                return $this->httpResponse()
                    ->setError()
                    ->setMessage('Could not delete the campaign on Meta, so it was kept here: ' . ($this->metaError($result, $adAccount, 'campaign delete') ?? 'Unknown error'));
            }
        }

        $campaign->delete();

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->setMessage('Campaign deleted.');
    }

    public function toggleStatus(int $id)
    {
        $campaign  = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);
        $newStatus = $campaign->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';

        // Change Meta first and only record the new status once Meta accepted it,
        // so the dashboard never shows PAUSED while the campaign is still spending.
        if ($campaign->meta_campaign_id) {
            $adAccount = $this->getConnectedAccount();
            if (! $adAccount) {
                return $this->httpResponse()
                    ->setError()
                    ->setMessage('Reconnect your Facebook account to change this campaign\'s status.');
            }

            $result = app(MetaApiClient::class)->updateCampaign($adAccount->access_token, $campaign->meta_campaign_id, [
                'status' => $newStatus,
            ]);

            if ($error = $this->metaError($result, $adAccount, 'campaign status change')) {
                return $this->httpResponse()->setError()->setMessage('Status not changed: ' . $error);
            }
        }

        $campaign->update(['status' => $newStatus]);

        return $this->httpResponse()->setMessage('Campaign status updated.');
    }

    public function pushToMeta(int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        if ($campaign->meta_campaign_id) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('This campaign is already on Meta (ID: ' . $campaign->meta_campaign_id . ').');
        }

        $adAccount = $this->getConnectedAccount();
        if (! $adAccount) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('No connected Meta ad account found. Please reconnect your Facebook account.');
        }

        $result = $this->syncCampaignToMeta($campaign, $adAccount);

        if ($result['success']) {
            return $this->httpResponse()->setMessage('Campaign pushed to Meta successfully! Campaign ID: ' . $result['meta_campaign_id']);
        }

        return $this->httpResponse()
            ->setError()
            ->setMessage('Failed to push campaign to Meta: ' . $result['error']);
    }

    /**
     * Creates the campaign on Meta (without budget — budget lives at ad set level).
     * Returns ['success' => bool, 'meta_campaign_id' => string|null, 'error' => string|null]
     */
    private function syncCampaignToMeta(MetaCampaign $campaign, MetaAdAccount $adAccount): array
    {
        try {
            $payload = [
                'name'                            => $campaign->name,
                'objective'                       => $campaign->objective,
                'status'                          => 'PAUSED',
                'special_ad_categories'           => [],
                'is_adset_budget_sharing_enabled' => false,
                // No daily_budget / lifetime_budget — budget lives at ad set level.
                // is_adset_budget_sharing_enabled must be explicitly false when no campaign budget (error 4834011).
            ];

            Log::info('Meta createCampaign payload', ['payload' => $payload, 'campaign_id' => $campaign->id]);

            $result = app(MetaApiClient::class)
                ->createCampaign($adAccount->access_token, $adAccount->ad_account_id, $payload);

            Log::info('Meta createCampaign response', ['response' => $result, 'campaign_id' => $campaign->id]);

            if (! empty($result['id'])) {
                $campaign->update(['meta_campaign_id' => $result['id']]);
                return ['success' => true, 'meta_campaign_id' => $result['id'], 'error' => null];
            }

            $errorMsg = $this->metaError($result, $adAccount, 'campaign create') ?? 'Meta returned no campaign ID.';

            return ['success' => false, 'meta_campaign_id' => null, 'error' => $errorMsg];
        } catch (\Throwable $e) {
            Log::error('Meta campaign push failed', ['error' => $e->getMessage(), 'campaign_id' => $campaign->id]);
            return ['success' => false, 'meta_campaign_id' => null, 'error' => $e->getMessage()];
        }
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
