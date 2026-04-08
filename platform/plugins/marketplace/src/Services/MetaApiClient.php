<?php

namespace Botble\Marketplace\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaApiClient
{
    protected string $version;
    protected string $baseUrl;

    public function __construct(string $version = 'v21.0')
    {
        $this->version = $version;
        $this->baseUrl = "https://graph.facebook.com/{$version}";
    }

    /**
     * Exchange auth code for short-lived token.
     */
    public function exchangeCodeForToken(string $code, string $appId, string $appSecret, string $redirectUri): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/oauth/access_token", [
                'client_id'     => $appId,
                'client_secret' => $appSecret,
                'redirect_uri'  => $redirectUri,
                'code'          => $code,
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::exchangeCodeForToken failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Extend a short-lived token to a long-lived token (~60 days).
     */
    public function extendToken(string $shortToken, string $appId, string $appSecret): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/oauth/access_token", [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => $appId,
                'client_secret'     => $appSecret,
                'fb_exchange_token' => $shortToken,
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::extendToken failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get current user info (id, name).
     */
    public function getMe(string $accessToken): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/me", [
                'access_token' => $accessToken,
                'fields'       => 'id,name,email',
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getMe failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get details for a single ad account including payment/status info.
     * Returns: account_status (int), funding_source_details, currency, disable_reason
     */
    public function getAdAccountDetails(string $accessToken, string $adAccountId): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/act_{$adAccountId}", [
                'access_token' => $accessToken,
                'fields'       => 'id,name,account_status,disable_reason,currency,funding_source_details',
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getAdAccountDetails failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all ad accounts the user has access to.
     */
    public function getAdAccounts(string $accessToken): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/me/adaccounts", [
                'access_token' => $accessToken,
                'fields'       => 'id,name,account_status,currency',
                'limit'        => 50,
            ]);

            return $response->json()['data'] ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getAdAccounts failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all Facebook Pages the user manages.
     */
    public function getPages(string $accessToken): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/me/accounts", [
                'access_token' => $accessToken,
                'fields'       => 'id,name',
                'limit'        => 50,
            ]);

            return $response->json()['data'] ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getPages failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ─── CAMPAIGNS ────────────────────────────────────────────────────────────

    public function createCampaign(string $accessToken, string $adAccountId, array $data): array
    {
        try {
            $response = Http::asJson()->post("{$this->baseUrl}/act_{$adAccountId}/campaigns", array_merge($data, [
                'access_token' => $accessToken,
            ]));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createCampaign failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateCampaign(string $accessToken, string $metaCampaignId, array $data): array
    {
        try {
            $response = Http::asJson()->post("{$this->baseUrl}/{$metaCampaignId}", array_merge($data, [
                'access_token' => $accessToken,
            ]));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::updateCampaign failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteCampaign(string $accessToken, string $metaCampaignId): array
    {
        try {
            $response = Http::delete("{$this->baseUrl}/{$metaCampaignId}", [
                'access_token' => $accessToken,
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::deleteCampaign failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ─── AD SETS ──────────────────────────────────────────────────────────────

    public function createAdSet(string $accessToken, string $adAccountId, array $data): array
    {
        try {
            $response = Http::asJson()->post("{$this->baseUrl}/act_{$adAccountId}/adsets", array_merge($data, [
                'access_token' => $accessToken,
            ]));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createAdSet failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateAdSet(string $accessToken, string $metaAdSetId, array $data): array
    {
        try {
            $response = Http::asJson()->post("{$this->baseUrl}/{$metaAdSetId}", array_merge($data, [
                'access_token' => $accessToken,
            ]));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::updateAdSet failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteAdSet(string $accessToken, string $metaAdSetId): array
    {
        try {
            $response = Http::delete("{$this->baseUrl}/{$metaAdSetId}", [
                'access_token' => $accessToken,
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::deleteAdSet failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ─── ADS ──────────────────────────────────────────────────────────────────

    public function createAdCreative(string $accessToken, string $adAccountId, array $data): array
    {
        try {
            $response = Http::asJson()->post("{$this->baseUrl}/act_{$adAccountId}/adcreatives", array_merge($data, [
                'access_token' => $accessToken,
            ]));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createAdCreative failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function createAd(string $accessToken, string $adAccountId, array $data): array
    {
        try {
            $response = Http::asJson()->post("{$this->baseUrl}/act_{$adAccountId}/ads", array_merge($data, [
                'access_token' => $accessToken,
            ]));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createAd failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateAd(string $accessToken, string $metaAdId, array $data): array
    {
        try {
            $response = Http::asJson()->post("{$this->baseUrl}/{$metaAdId}", array_merge($data, [
                'access_token' => $accessToken,
            ]));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::updateAd failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteAd(string $accessToken, string $metaAdId): array
    {
        try {
            $response = Http::delete("{$this->baseUrl}/{$metaAdId}", [
                'access_token' => $accessToken,
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::deleteAd failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ─── INSIGHTS ─────────────────────────────────────────────────────────────

    /**
     * Fetch insights for a campaign, ad set, or ad.
     * $objectId can be a campaign_id, adset_id, or ad_id.
     */
    public function getInsights(string $accessToken, string $objectId, array $params = []): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/{$objectId}/insights", array_merge([
                'access_token' => $accessToken,
                'fields'       => 'impressions,clicks,spend,ctr,cpc',
                'date_preset'  => 'maximum',
            ], $params));

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getInsights failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Build the targeting spec array for ad set creation.
     */
    public function buildTargeting(array $adSet): array
    {
        $targeting = [
            'age_min' => (int) $adSet['targeting_age_min'],
            'age_max' => (int) $adSet['targeting_age_max'],
        ];

        // Genders: 1=male, 2=female
        if ($adSet['targeting_genders'] === 'male') {
            $targeting['genders'] = [1];
        } elseif ($adSet['targeting_genders'] === 'female') {
            $targeting['genders'] = [2];
        }

        // Geo locations (country codes)
        $locations = is_array($adSet['targeting_locations'])
            ? $adSet['targeting_locations']
            : json_decode($adSet['targeting_locations'] ?? '[]', true);

        if (! empty($locations)) {
            $targeting['geo_locations'] = ['countries' => array_values(array_filter($locations))];
        } else {
            $targeting['geo_locations'] = ['countries' => ['US']]; // default fallback
        }

        return $targeting;
    }
}
