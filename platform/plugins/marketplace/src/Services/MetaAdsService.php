<?php

namespace Botble\Marketplace\Services;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Exception;
use Illuminate\Support\Facades\Log;

class MetaAdsService
{
    public function __construct(private MetaApiClient $client) {}

    // -------------------------------------------------------------------------
    // OAuth
    // -------------------------------------------------------------------------

    public function getAuthUrl(string $redirectUri): string
    {
        $params = http_build_query([
            'client_id' => MarketplaceHelper::getMetaAppId(),
            'redirect_uri' => $redirectUri,
            'scope' => 'ads_management,ads_read,business_management',
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);

        return 'https://www.facebook.com/' . MarketplaceHelper::getMetaApiVersion() . '/dialog/oauth?' . $params;
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = $this->client->get('oauth/access_token', [
            'client_id' => MarketplaceHelper::getMetaAppId(),
            'client_secret' => MarketplaceHelper::getMetaAppSecret(),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        return $response;
    }

    public function getUserProfile(string $accessToken): array
    {
        return $this->client->withToken($accessToken)->get('me', ['fields' => 'id,name']);
    }

    public function getUserAdAccounts(string $accessToken): array
    {
        $response = $this->client->withToken($accessToken)->get('me/adaccounts', [
            'fields' => 'id,name,account_status',
        ]);

        return $response['data'] ?? [];
    }

    // -------------------------------------------------------------------------
    // Campaigns
    // -------------------------------------------------------------------------

    /**
     * Create a campaign on Meta and return its remote ID.
     */
    public function createCampaign(MetaAdAccount $account, array $data): string
    {
        $adAccountId = $account->ad_account_id; // e.g. "act_123456789"

        $payload = [
            'name' => $data['name'],
            'objective' => $data['objective'],
            'status' => $data['status'] ?? 'PAUSED',
        ];

        if (! empty($data['daily_budget'])) {
            // Meta expects budget in cents
            $payload['daily_budget'] = (int) round($data['daily_budget'] * 100);
        }

        if (! empty($data['lifetime_budget'])) {
            $payload['lifetime_budget'] = (int) round($data['lifetime_budget'] * 100);
        }

        if (! empty($data['start_date'])) {
            $payload['start_time'] = $data['start_date'];
        }

        if (! empty($data['end_date'])) {
            $payload['stop_time'] = $data['end_date'];
        }

        $response = $this->client
            ->withToken($account->access_token)
            ->post("{$adAccountId}/campaigns", $payload);

        return $response['id'];
    }

    public function updateCampaignStatus(MetaAdAccount $account, string $remoteId, string $status): void
    {
        $this->client
            ->withToken($account->access_token)
            ->post($remoteId, ['status' => $status]);
    }

    public function deleteCampaign(MetaAdAccount $account, string $remoteId): void
    {
        $this->client
            ->withToken($account->access_token)
            ->delete($remoteId);
    }

    // -------------------------------------------------------------------------
    // Ad Sets
    // -------------------------------------------------------------------------

    public function createAdSet(MetaAdAccount $account, string $remoteCampaignId, array $data): string
    {
        $adAccountId = $account->ad_account_id;

        $targeting = [
            'age_min' => $data['targeting_age_min'] ?? 18,
            'age_max' => $data['targeting_age_max'] ?? 65,
        ];

        if (! empty($data['targeting_locations'])) {
            $locations = is_string($data['targeting_locations'])
                ? json_decode($data['targeting_locations'], true)
                : $data['targeting_locations'];
            $targeting['geo_locations'] = ['countries' => $locations];
        }

        if (! empty($data['targeting_interests'])) {
            $interests = is_string($data['targeting_interests'])
                ? json_decode($data['targeting_interests'], true)
                : $data['targeting_interests'];
            $targeting['interests'] = $interests;
        }

        $payload = [
            'name' => $data['name'],
            'campaign_id' => $remoteCampaignId,
            'status' => $data['status'] ?? 'PAUSED',
            'optimization_goal' => $data['optimization_goal'] ?? 'LINK_CLICKS',
            'targeting' => json_encode($targeting),
            'billing_event' => 'IMPRESSIONS',
        ];

        if (! empty($data['daily_budget'])) {
            $payload['daily_budget'] = (int) round($data['daily_budget'] * 100);
        }

        $response = $this->client
            ->withToken($account->access_token)
            ->post("{$adAccountId}/adsets", $payload);

        return $response['id'];
    }

    public function updateAdSet(MetaAdAccount $account, string $remoteId, array $data): void
    {
        $payload = array_filter([
            'name' => $data['name'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        if (! empty($payload)) {
            $this->client
                ->withToken($account->access_token)
                ->post($remoteId, $payload);
        }
    }

    public function deleteAdSet(MetaAdAccount $account, string $remoteId): void
    {
        $this->client
            ->withToken($account->access_token)
            ->delete($remoteId);
    }

    // -------------------------------------------------------------------------
    // Ads
    // -------------------------------------------------------------------------

    public function createAd(MetaAdAccount $account, string $remoteAdSetId, array $data): string
    {
        $adAccountId = $account->ad_account_id;

        // Build creative payload
        $creative = [
            'name' => $data['name'] . ' Creative',
            'object_story_spec' => json_encode([
                'page_id' => $account->fb_user_id,
                'link_data' => [
                    'link' => $data['destination_url'] ?? '',
                    'message' => $data['primary_text'] ?? '',
                    'name' => $data['headline'] ?? '',
                    'description' => $data['description'] ?? '',
                    'call_to_action' => [
                        'type' => $data['cta_button'] ?? 'LEARN_MORE',
                        'value' => ['link' => $data['destination_url'] ?? ''],
                    ],
                ],
            ]),
        ];

        if (! empty($data['image_url'])) {
            $creative['object_story_spec'] = json_encode(
                array_merge(json_decode($creative['object_story_spec'], true), [
                    'link_data' => array_merge(
                        json_decode($creative['object_story_spec'], true)['link_data'],
                        ['picture' => $data['image_url']]
                    ),
                ])
            );
        }

        $creativeResponse = $this->client
            ->withToken($account->access_token)
            ->post("{$adAccountId}/adcreatives", $creative);

        $adPayload = [
            'name' => $data['name'],
            'adset_id' => $remoteAdSetId,
            'creative' => json_encode(['creative_id' => $creativeResponse['id']]),
            'status' => $data['status'] ?? 'PAUSED',
        ];

        $response = $this->client
            ->withToken($account->access_token)
            ->post("{$adAccountId}/ads", $adPayload);

        return $response['id'];
    }

    public function updateAd(MetaAdAccount $account, string $remoteId, array $data): void
    {
        $payload = array_filter([
            'name' => $data['name'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        if (! empty($payload)) {
            $this->client
                ->withToken($account->access_token)
                ->post($remoteId, $payload);
        }
    }

    public function deleteAd(MetaAdAccount $account, string $remoteId): void
    {
        $this->client
            ->withToken($account->access_token)
            ->delete($remoteId);
    }

    // -------------------------------------------------------------------------
    // Insights
    // -------------------------------------------------------------------------

    public function fetchCampaignInsights(MetaAdAccount $account, string $remoteCampaignId): array
    {
        try {
            return $this->client
                ->withToken($account->access_token)
                ->get("{$remoteCampaignId}/insights", [
                    'fields' => 'impressions,clicks,spend',
                    'date_preset' => 'last_30_days',
                ]);
        } catch (Exception $e) {
            Log::warning('Meta Ads: could not fetch insights', ['error' => $e->getMessage()]);

            return [];
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Silently call $callable and log any Meta API exception.
     * Used by controllers to avoid hard-failing when the API is unavailable.
     */
    public function safely(callable $callable): mixed
    {
        try {
            return $callable();
        } catch (Exception $e) {
            Log::error('Meta Ads API call failed', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
