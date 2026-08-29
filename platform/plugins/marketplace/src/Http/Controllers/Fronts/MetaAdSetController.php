<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Facades\Assets;
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

        Assets::addScriptsDirectly(['vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.min.js'])
            ->addStylesDirectly(['vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.css']);
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

        $validGoals = $this->validGoalsForObjective($campaign->objective);

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'daily_budget'        => ['required', 'numeric', 'min:1'],
            'bid_cap'             => ['nullable', 'numeric', 'min:1'],
            'targeting_age_min'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_age_max'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_genders'   => ['required', 'in:all,male,female'],
            'optimization_goal'   => ['required', 'in:' . implode(',', $validGoals)],
            'targeting_locations' => ['nullable', 'string'],
            'targeting_interests' => ['nullable', 'string'],
            'placements'          => ['nullable', 'array'],
        ]);

        $validated['campaign_id'] = $campaign->id;
        $validated['store_id']    = $this->storeId;
        $validated['status']      = 'PAUSED';

        // targeting_locations / targeting_interests arrive as JSON strings of
        // structured objects (from the location-picker / interest-picker widgets).
        $validated['targeting_locations'] = $this->parseJsonArrayInput($validated['targeting_locations'] ?? null);
        $validated['targeting_interests'] = $this->parseJsonArrayInput($validated['targeting_interests'] ?? null);

        $adSet = MetaAdSet::query()->create($validated);

        if ($campaign->meta_campaign_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                $this->syncAdSetToMeta($adSet, $campaign->meta_campaign_id, $adAccount);
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

        $dailySeries = $adSet->dailyInsights()->orderBy('date')->get();

        $chartData = [
            'dates'  => $dailySeries->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->values(),
            'spend'  => $dailySeries->pluck('spend')->map(fn ($v) => (float) $v)->values(),
            'clicks' => $dailySeries->pluck('clicks')->map(fn ($v) => (int) $v)->values(),
        ];

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.show', compact('adSet', 'chartData'));
    }

    public function edit(int $id)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($id);

        $this->pageTitle('Edit: ' . $adSet->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ad-sets.edit', compact('adSet'));
    }

    public function update(Request $request, int $id)
    {
        $adSet    = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($id);
        $campaign = $adSet->campaign;

        $validGoals = $campaign ? $this->validGoalsForObjective($campaign->objective) : $this->allGoals();

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'daily_budget'        => ['required', 'numeric', 'min:1'],
            'bid_cap'             => ['nullable', 'numeric', 'min:1'],
            'targeting_age_min'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_age_max'   => ['required', 'integer', 'min:13', 'max:65'],
            'targeting_genders'   => ['required', 'in:all,male,female'],
            'optimization_goal'   => ['required', 'in:' . implode(',', $validGoals)],
            'targeting_locations' => ['nullable', 'string'],
            'targeting_interests' => ['nullable', 'string'],
            'placements'          => ['nullable', 'array'],
        ]);

        $validated['targeting_locations'] = $this->parseJsonArrayInput($validated['targeting_locations'] ?? null);
        $validated['targeting_interests'] = $this->parseJsonArrayInput($validated['targeting_interests'] ?? null);

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
                        'targeting_interests' => $adSet->targeting_interests,
                        'placements'          => $adSet->placements,
                    ]);

                    $payload = [
                        'name'         => $adSet->name,
                        'daily_budget' => (int) ($adSet->daily_budget * 100),
                        'targeting'    => $targeting,
                        'status'       => $adSet->status,
                    ];
                    if (! empty($adSet->bid_cap)) {
                        $payload['bid_strategy'] = 'LOWEST_COST_WITH_BID_CAP';
                        $payload['bid_amount']   = (int) ($adSet->bid_cap * 100);
                    }

                    $metaClient->updateAdSet($adAccount->access_token, $adSet->meta_adset_id, $payload);
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

    /**
     * Re-push an existing ad set (with meta_adset_id = NULL) to Meta Ads Manager.
     */
    public function pushToMeta(int $id)
    {
        $adSet    = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($id);
        $campaign = $adSet->campaign;

        if (! $campaign || ! $campaign->meta_campaign_id) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('Campaign is not synced to Meta. Please push the campaign to Meta first.');
        }

        // Guard: optimization_goal must be compatible with campaign objective
        $validGoals = $this->validGoalsForObjective($campaign->objective);
        if (! in_array($adSet->optimization_goal, $validGoals)) {
            return $this->httpResponse()
                ->setError()
                ->setMessage(sprintf(
                    'Cannot push: optimization goal "%s" is incompatible with campaign objective "%s". '
                    . 'Please edit this ad set and change the optimization goal to one of: %s.',
                    $adSet->optimization_goal,
                    str_replace('OUTCOME_', '', $campaign->objective),
                    implode(', ', $validGoals)
                ));
        }

        $adAccount = $this->getConnectedAccount();
        if (! $adAccount) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('No connected Meta ad account found. Please reconnect your Facebook account.');
        }

        $result = $this->syncAdSetToMeta($adSet, $campaign->meta_campaign_id, $adAccount);

        if ($result['success']) {
            return $this->httpResponse()->setMessage('Ad set pushed to Meta successfully! Ad Set ID: ' . $result['meta_adset_id']);
        }

        return $this->httpResponse()
            ->setError()
            ->setMessage('Failed to push to Meta: ' . $result['error']);
    }

    /**
     * Internal helper — creates the ad set on Meta and saves the returned ID.
     * Returns ['success' => bool, 'meta_adset_id' => string|null, 'error' => string|null]
     */
    private function syncAdSetToMeta(MetaAdSet $adSet, string $metaCampaignId, MetaAdAccount $adAccount): array
    {
        try {
            $metaClient = app(MetaApiClient::class);
            $targeting  = $metaClient->buildTargeting([
                'targeting_age_min'   => $adSet->targeting_age_min,
                'targeting_age_max'   => $adSet->targeting_age_max,
                'targeting_genders'   => $adSet->targeting_genders,
                'targeting_locations' => $adSet->targeting_locations,
                'targeting_interests' => $adSet->targeting_interests,
                'placements'          => $adSet->placements,
            ]);

            $payload = [
                'name'              => $adSet->name,
                'campaign_id'       => $metaCampaignId,
                'daily_budget'      => (int) ($adSet->daily_budget * 100),
                'billing_event'     => 'IMPRESSIONS',
                'optimization_goal' => $adSet->optimization_goal,
                'bid_strategy'      => 'LOWEST_COST_WITHOUT_CAP',
                'targeting'         => $targeting,
                'status'            => 'PAUSED',
            ];
            // Override bid strategy when vendor explicitly provided a bid cap.
            if (! empty($adSet->bid_cap)) {
                $payload['bid_strategy'] = 'LOWEST_COST_WITH_BID_CAP';
                $payload['bid_amount']   = (int) ($adSet->bid_cap * 100);
            }

            Log::info('Meta createAdSet payload', ['payload' => $payload, 'adset_id' => $adSet->id]);

            $result = $metaClient->createAdSet($adAccount->access_token, $adAccount->ad_account_id, $payload);

            Log::info('Meta createAdSet response', ['response' => $result, 'adset_id' => $adSet->id]);

            if (! empty($result['id'])) {
                $adSet->update(['meta_adset_id' => $result['id']]);
                return ['success' => true, 'meta_adset_id' => $result['id'], 'error' => null];
            }

            // Build a detailed error message including subcode and user message
            $err        = $result['error'] ?? [];
            $errorMsg   = ($err['message'] ?? 'Unknown error')
                . (isset($err['error_subcode']) ? ' (subcode: ' . $err['error_subcode'] . ')' : '')
                . (isset($err['error_user_msg']) ? ' — ' . $err['error_user_msg'] : '');
            Log::warning('Meta ad set create API error', ['error' => $err, 'adset_id' => $adSet->id]);
            return ['success' => false, 'meta_adset_id' => null, 'error' => $errorMsg];
        } catch (\Throwable $e) {
            Log::error('Meta ad set push failed', ['error' => $e->getMessage(), 'adset_id' => $adSet->id]);
            return ['success' => false, 'meta_adset_id' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * AJAX: search Meta geo locations filtered by type.
     */
    public function searchLocations(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $adAccount = $this->getConnectedAccount();
            if (! $adAccount) {
                return response()->json(['error' => 'No connected Meta ad account. Please reconnect Facebook.'], 403);
            }

            $typeMap = [
                'country' => ['country'],
                'region'  => ['region'],
                'city'    => ['city'],
                'zip'     => ['zip'],
            ];
            $tab   = $request->get('tab', 'country');
            $types = $typeMap[$tab] ?? ['country'];

            $extra = [];
            if ($request->filled('country_code') && $tab !== 'country') {
                $extra['country_code'] = $request->get('country_code');
            }

            $results = app(MetaApiClient::class)->searchLocations($adAccount->access_token, $query, $types, $extra);

            $mapped = array_map(fn ($r) => [
                'key'          => $r['key'] ?? '',
                'name'         => $r['name'] ?? '',
                'type'         => $r['type'] ?? $tab,
                'country_code' => $r['country_code'] ?? null,
                'country_name' => $r['country_name'] ?? null,
                'region'       => $r['region'] ?? null,
                'label'        => ($r['name'] ?? '')
                    . (isset($r['region'])       ? ', ' . $r['region']       : '')
                    . (isset($r['country_name']) ? ', ' . $r['country_name'] : ''),
            ], $results);

            return response()->json(array_values($mapped));
        } catch (\Throwable $e) {
            Log::error('MetaAdSetController::searchLocations error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Parse a JSON-string-of-objects input (used by both the location-picker and
     * interest-picker widgets, e.g. [{key,type,name}] or [{id,name}]).
     * Accepts a legacy plain comma-separated string as a fallback for old rows
     * saved before either picker existed (kept as opaque strings; buildTargeting()
     * already knows to skip interest entries with no 'id').
     */
    private function parseJsonArrayInput(?string $input): array
    {
        if (empty($input)) {
            return [];
        }

        $decoded = json_decode($input, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return array_values(array_filter(array_map('trim', explode(',', $input))));
    }

    /**
     * AJAX: search Meta's real interest targeting index.
     */
    public function searchInterests(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $adAccount = $this->getConnectedAccount();
            if (! $adAccount) {
                return response()->json(['error' => 'No connected Meta ad account. Please reconnect Facebook.'], 403);
            }

            $results = app(MetaApiClient::class)->searchInterests($adAccount->access_token, $query);

            return response()->json(array_values(array_map(fn ($r) => [
                'id'   => $r['id'] ?? '',
                'name' => $r['name'] ?? '',
                'audience_size_lower_bound' => $r['audience_size_lower_bound'] ?? null,
            ], $results)));
        } catch (\Throwable $e) {
            Log::error('MetaAdSetController::searchInterests error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: live reachable-audience-size estimate while building targeting,
     * so vendors get feedback before pushing the ad set to Meta.
     */
    public function deliveryEstimate(Request $request)
    {
        $adAccount = $this->getConnectedAccount();
        if (! $adAccount) {
            return response()->json(['error' => 'No connected Meta ad account. Please reconnect Facebook.'], 403);
        }

        try {
            $metaClient = app(MetaApiClient::class);
            $targeting  = $metaClient->buildTargeting([
                'targeting_age_min'   => (int) $request->input('targeting_age_min', 18),
                'targeting_age_max'   => (int) $request->input('targeting_age_max', 65),
                'targeting_genders'   => $request->input('targeting_genders', 'all'),
                'targeting_locations' => $this->parseJsonArrayInput($request->input('targeting_locations')),
                'targeting_interests' => $this->parseJsonArrayInput($request->input('targeting_interests')),
                'placements'          => $request->input('placements', []),
            ]);

            $estimate = $metaClient->getDeliveryEstimate(
                $adAccount->access_token,
                $adAccount->ad_account_id,
                $targeting,
                $request->input('optimization_goal', 'REACH')
            );

            if (empty($estimate)) {
                return response()->json(['error' => 'Estimate unavailable right now.'], 200);
            }

            return response()->json([
                'estimate_mau_lower' => $estimate['estimate_mau_lower_bound'] ?? null,
                'estimate_mau_upper' => $estimate['estimate_mau_upper_bound'] ?? null,
                'estimate_dau_lower' => $estimate['estimate_dau_lower_bound'] ?? null,
                'estimate_dau_upper' => $estimate['estimate_dau_upper_bound'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('MetaAdSetController::deliveryEstimate error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Estimate failed: ' . $e->getMessage()], 200);
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

    private function validGoalsForObjective(string $objective): array
    {
        return match ($objective) {
            'OUTCOME_TRAFFIC'    => ['LINK_CLICKS', 'LANDING_PAGE_VIEWS', 'IMPRESSIONS'],
            'OUTCOME_AWARENESS'  => ['REACH', 'IMPRESSIONS', 'VIDEO_VIEWS'],
            'OUTCOME_ENGAGEMENT' => ['POST_ENGAGEMENT', 'VIDEO_VIEWS', 'IMPRESSIONS'],
            'OUTCOME_SALES'      => ['LINK_CLICKS', 'IMPRESSIONS'],
            'OUTCOME_LEADS'      => ['LINK_CLICKS', 'IMPRESSIONS'],
            default              => $this->allGoals(),
        };
    }

    private function allGoals(): array
    {
        return ['LINK_CLICKS', 'IMPRESSIONS', 'REACH', 'LANDING_PAGE_VIEWS', 'POST_ENGAGEMENT', 'VIDEO_VIEWS', 'OFFSITE_CONVERSIONS'];
    }
}
