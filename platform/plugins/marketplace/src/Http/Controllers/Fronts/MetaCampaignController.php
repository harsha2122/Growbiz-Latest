<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        // Live check: payment method
        $hasPaymentMethod = null;
        $accountStatus    = null;
        $adAccount        = $this->getConnectedAccount();

        if ($adAccount) {
            $details = app(MetaApiClient::class)
                ->getAdAccountDetails($adAccount->access_token, $adAccount->ad_account_id);

            if (! empty($details['account_status'])) {
                $accountStatus    = (int) $details['account_status'];
                $hasPaymentMethod = ! empty($details['funding_source_details']);
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

        // Push to Meta API
        $adAccount = $this->getConnectedAccount();
        if ($adAccount) {
            $this->syncCampaignToMeta($campaign, $adAccount);
        }

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id))
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
            'objective'       => ['required', 'in:OUTCOME_TRAFFIC,OUTCOME_AWARENESS,OUTCOME_ENGAGEMENT,OUTCOME_SALES,OUTCOME_LEADS,OUTCOME_APP_PROMOTION'],
            'daily_budget'    => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $campaign->update($validated);

        if ($campaign->meta_campaign_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->updateCampaign($adAccount->access_token, $campaign->meta_campaign_id, [
                        'name'   => $campaign->name,
                        'status' => $campaign->status,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Meta campaign update API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

        if ($campaign->meta_campaign_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->deleteCampaign($adAccount->access_token, $campaign->meta_campaign_id);
                } catch (\Throwable $e) {
                    Log::error('Meta campaign delete API failed', ['error' => $e->getMessage()]);
                }
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
        $campaign->update(['status' => $newStatus]);

        if ($campaign->meta_campaign_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->updateCampaign($adAccount->access_token, $campaign->meta_campaign_id, [
                        'status' => $newStatus,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Meta campaign toggleStatus API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $this->httpResponse()->setMessage('Campaign status updated.');
    }

    public function pushToMeta(int $id)
    {
        $campaign = MetaCampaign::query()->where('store_id', $this->storeId)->findOrFail($id);

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

            $err      = $result['error'] ?? [];
            $errorMsg = ($err['message'] ?? 'Unknown error')
                . (isset($err['error_subcode']) ? ' (subcode: ' . $err['error_subcode'] . ')' : '')
                . (isset($err['error_user_msg']) ? ' — ' . $err['error_user_msg'] : '')
                . (isset($err['error_user_title']) ? ' [' . $err['error_user_title'] . ']' : '');
            Log::warning('Meta campaign create API error', ['error' => $err, 'campaign_id' => $campaign->id]);

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
