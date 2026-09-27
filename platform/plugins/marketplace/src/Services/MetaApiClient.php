<?php

namespace Botble\Marketplace\Services;

use Illuminate\Http\Client\PendingRequest;
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
     * Base request with timeouts, and the token sent as a Bearer header rather than a
     * URL query param so it never lands in web server / proxy access logs.
     */
    protected function http(?string $accessToken = null): PendingRequest
    {
        $request = Http::timeout(20)->connectTimeout(10);

        return $accessToken ? $request->withToken($accessToken) : $request;
    }

    /**
     * Human-readable message for a Graph API error response, or null when the
     * response is not an error.
     */
    public static function errorMessage(array $result): ?string
    {
        if (! array_key_exists('error', $result)) {
            return null;
        }

        $error = $result['error'];

        if (! is_array($error)) {
            return (string) $error ?: 'Unknown Meta API error';
        }

        return ($error['error_user_msg'] ?? $error['message'] ?? 'Unknown Meta API error')
            . (isset($error['error_subcode']) ? ' (subcode: ' . $error['error_subcode'] . ')' : '');
    }

    /**
     * Graph error code 190 = access token expired, revoked, or otherwise invalid.
     */
    public static function isTokenError(array $result): bool
    {
        return is_array($result['error'] ?? null) && (int) ($result['error']['code'] ?? 0) === 190;
    }

    /**
     * A delete counts as done if Meta confirms it, or reports the object no longer
     * exists (already deleted on Meta's side) - either way nothing is left running.
     */
    public static function isDeleted(array $result): bool
    {
        if (! empty($result['success'])) {
            return true;
        }

        $error = $result['error'] ?? null;

        return is_array($error) && (int) ($error['code'] ?? 0) === 100 && (int) ($error['error_subcode'] ?? 0) === 33;
    }

    /**
     * Meta takes money in the currency's minor unit (paise/cents). round() rather
     * than (int) cast: 19.99 * 100 is 1998.9999... and would truncate to 1998.
     */
    public static function toMinorUnits(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    /**
     * Exchange auth code for short-lived token.
     */
    public function exchangeCodeForToken(string $code, string $appId, string $appSecret, string $redirectUri): array
    {
        try {
            $response = $this->http()->get("{$this->baseUrl}/oauth/access_token", [
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
            $response = $this->http()->get("{$this->baseUrl}/oauth/access_token", [
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
            $response = $this->http($accessToken)->get("{$this->baseUrl}/me", [
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
     * Returns: account_status (int), funding_source_details, currency, disable_reason,
     * amount_spent, spend_cap, balance, timezone_name.
     */
    public function getAdAccountDetails(string $accessToken, string $adAccountId): array
    {
        try {
            $response = $this->http($accessToken)->get("{$this->baseUrl}/act_{$adAccountId}", [
                'fields'       => 'id,name,account_status,disable_reason,currency,funding_source_details,'
                    . 'amount_spent,spend_cap,balance,timezone_name',
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getAdAccountDetails failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get all ad accounts the user has access to.
     */
    public function getAdAccounts(string $accessToken): array
    {
        try {
            $response = $this->http($accessToken)->get("{$this->baseUrl}/me/adaccounts", [
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
            $response = $this->http($accessToken)->get("{$this->baseUrl}/me/accounts", [
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
            $response = $this->http($accessToken)->asJson()->post("{$this->baseUrl}/act_{$adAccountId}/campaigns", $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createCampaign failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateCampaign(string $accessToken, string $metaCampaignId, array $data): array
    {
        try {
            $response = $this->http($accessToken)->asJson()->post("{$this->baseUrl}/{$metaCampaignId}", $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::updateCampaign failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteCampaign(string $accessToken, string $metaCampaignId): array
    {
        try {
            $response = $this->http($accessToken)->delete("{$this->baseUrl}/{$metaCampaignId}");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::deleteCampaign failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    // ─── AD SETS ──────────────────────────────────────────────────────────────

    public function createAdSet(string $accessToken, string $adAccountId, array $data): array
    {
        try {
            $response = $this->http($accessToken)->asJson()->post("{$this->baseUrl}/act_{$adAccountId}/adsets", $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createAdSet failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateAdSet(string $accessToken, string $metaAdSetId, array $data): array
    {
        try {
            $response = $this->http($accessToken)->asJson()->post("{$this->baseUrl}/{$metaAdSetId}", $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::updateAdSet failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteAdSet(string $accessToken, string $metaAdSetId): array
    {
        try {
            $response = $this->http($accessToken)->delete("{$this->baseUrl}/{$metaAdSetId}");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::deleteAdSet failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    // ─── ADS ──────────────────────────────────────────────────────────────────

    public function createAdCreative(string $accessToken, string $adAccountId, array $data): array
    {
        try {
            $response = $this->http($accessToken)->asJson()->post("{$this->baseUrl}/act_{$adAccountId}/adcreatives", $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createAdCreative failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function createAd(string $accessToken, string $adAccountId, array $data): array
    {
        try {
            $response = $this->http($accessToken)->asJson()->post("{$this->baseUrl}/act_{$adAccountId}/ads", $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::createAd failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function updateAd(string $accessToken, string $metaAdId, array $data): array
    {
        try {
            $response = $this->http($accessToken)->asJson()->post("{$this->baseUrl}/{$metaAdId}", $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::updateAd failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteAd(string $accessToken, string $metaAdId): array
    {
        try {
            $response = $this->http($accessToken)->delete("{$this->baseUrl}/{$metaAdId}");

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::deleteAd failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    // ─── INSIGHTS ─────────────────────────────────────────────────────────────

    /**
     * The full basic metric set worth pulling for every object — cheap fields
     * that don't require an actions/conversions breakdown.
     */
    public const BASIC_INSIGHT_FIELDS = 'impressions,clicks,spend,reach,frequency,cpm,cpc,ctr,unique_clicks';

    /**
     * Fields that carry conversion data — kept separate from BASIC_INSIGHT_FIELDS
     * since "actions"/"action_values" are nested arrays that need their own parsing.
     */
    public const CONVERSION_INSIGHT_FIELDS = 'actions,action_values';

    /**
     * Ad-level relevance diagnostics — only meaningful for ads, not campaigns/ad sets.
     */
    public const RANKING_INSIGHT_FIELDS = 'quality_ranking,engagement_rate_ranking,conversion_rate_ranking';

    /**
     * Fetch insights for a campaign, ad set, or ad.
     * $objectId can be a campaign_id, adset_id, or ad_id.
     *
     * Pass 'fields' in $params to override the default basic metric set.
     * Pass 'breakdowns' => ['age', 'gender'] (or ['publisher_platform', 'platform_position'], etc.)
     * to get one row per breakdown combination instead of a single cumulative row.
     * Pass 'time_increment' => 1 with 'time_range' => ['since' => 'Y-m-d', 'until' => 'Y-m-d']
     * to get one row per day instead of a single lifetime row (date_preset is ignored then).
     *
     * Returns the raw decoded response (['data' => [...], 'paging' => [...]] or ['error' => [...]]).
     */
    public function getInsights(string $accessToken, string $objectId, array $params = []): array
    {
        try {
            $query = array_merge([
                'fields'       => self::BASIC_INSIGHT_FIELDS,
                'date_preset'  => 'maximum',
                'limit'        => 500,
            ], $params);

            if (isset($query['time_range']) && is_array($query['time_range'])) {
                $query['time_range'] = json_encode($query['time_range']);
                unset($query['date_preset']);
            }

            if (isset($query['breakdowns']) && is_array($query['breakdowns'])) {
                $query['breakdowns'] = implode(',', $query['breakdowns']);
            }

            $response = $this->http($accessToken)->get("{$this->baseUrl}/{$objectId}/insights", $query);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getInsights failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Sum a Meta "actions" array (list of ['action_type' => ..., 'value' => ...])
     * down to a single conversions count, counting only purchase/lead-style actions.
     */
    public static function sumConversionActions(array $actions): int
    {
        $conversionTypes = ['purchase', 'offsite_conversion.fb_pixel_purchase', 'lead', 'complete_registration', 'onsite_conversion.purchase'];

        return (int) collect($actions)
            ->filter(fn ($action) => in_array($action['action_type'] ?? '', $conversionTypes))
            ->sum(fn ($action) => (float) ($action['value'] ?? 0));
    }

    /**
     * Sum a Meta "action_values" array down to a single conversion value (revenue).
     */
    public static function sumConversionValues(array $actionValues): float
    {
        $conversionTypes = ['purchase', 'offsite_conversion.fb_pixel_purchase', 'onsite_conversion.purchase'];

        return (float) collect($actionValues)
            ->filter(fn ($action) => in_array($action['action_type'] ?? '', $conversionTypes))
            ->sum(fn ($action) => (float) ($action['value'] ?? 0));
    }

    /**
     * Estimate reachable audience size for a targeting spec while building an ad set,
     * so vendors get live "X - Y people" feedback instead of guessing.
     */
    public function getDeliveryEstimate(string $accessToken, string $adAccountId, array $targeting, string $optimizationGoal = 'REACH'): array
    {
        try {
            $response = $this->http($accessToken)->get("{$this->baseUrl}/act_{$adAccountId}/delivery_estimate", [
                'targeting_spec'    => json_encode($targeting),
                'optimization_goal' => $optimizationGoal,
            ]);

            return $response->json()['data'][0] ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getDeliveryEstimate failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Search Meta's interest targeting index (type=adinterest) so vendors pick real,
     * ID-backed interests instead of typing free text that Meta would silently reject.
     */
    public function searchInterests(string $accessToken, string $query): array
    {
        try {
            $response = $this->http($accessToken)->get("{$this->baseUrl}/search", [
                'type'         => 'adinterest',
                'q'            => $query,
                'limit'        => 20,
            ]);

            return $response->json()['data'] ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::searchInterests failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get Meta's own real ad preview markup (an iframe-ready HTML snippet) for an
     * already-created ad, in a given placement format (e.g. DESKTOP_FEED_STANDARD,
     * MOBILE_FEED_STANDARD, INSTAGRAM_STANDARD, INSTAGRAM_STORY).
     */
    public function getAdPreview(string $accessToken, string $metaAdId, string $adFormat = 'MOBILE_FEED_STANDARD'): array
    {
        try {
            $response = $this->http($accessToken)->get("{$this->baseUrl}/{$metaAdId}/previews", [
                'ad_format'    => $adFormat,
            ]);

            $json = $response->json();

            return $json['data'][0] ?? ($json['error'] ?? []);
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getAdPreview failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch Meta's own optimization recommendations for a campaign/ad set/ad
     * (e.g. "increase your budget", "your audience is too narrow").
     */
    public function getRecommendations(string $accessToken, string $objectId): array
    {
        try {
            $response = $this->http($accessToken)->get("{$this->baseUrl}/{$objectId}", [
                'fields'       => 'recommendations',
            ]);

            return $response->json()['recommendations'] ?? [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::getRecommendations failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Search Meta's geo location index (countries, regions, cities, zips).
     * Returns array of [{key, name, type, country_code, ...}]
     */
    public function searchLocations(string $accessToken, string $query, array $types = [], array $extra = []): array
    {
        try {
            $params = array_merge([
                'type'           => 'adgeolocation',
                'q'              => $query,
                'location_types' => json_encode($types ?: ['country', 'region', 'city', 'zip']),
            ], $extra);

            $response = $this->http($accessToken)->get("{$this->baseUrl}/search", $params);
            $json     = $response->json();

            return is_array($json) ? ($json['data'] ?? []) : [];
        } catch (\Throwable $e) {
            Log::error('MetaApiClient::searchLocations failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Build the targeting spec array for ad set creation.
     * targeting_locations is now an array of structured objects:
     *   [{key, type, name}] where type is country|region|city|zip
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

        $locations = is_array($adSet['targeting_locations'])
            ? $adSet['targeting_locations']
            : json_decode($adSet['targeting_locations'] ?? '[]', true);

        $geoLocations = [];

        if (! empty($locations)) {
            foreach ($locations as $loc) {
                // Support both old plain string format (country codes) and new structured format
                if (is_string($loc)) {
                    $geoLocations['countries'][] = strtoupper(trim($loc));
                    continue;
                }

                $type = $loc['type'] ?? 'country';
                $key  = $loc['key'] ?? null;

                if (! $key) {
                    continue;
                }

                switch ($type) {
                    case 'country':
                        $geoLocations['countries'][] = $key;
                        break;
                    case 'region':
                        $geoLocations['regions'][] = ['key' => $key];
                        break;
                    case 'city':
                        $geoLocations['cities'][] = ['key' => $key];
                        break;
                    case 'zip':
                        $geoLocations['zips'][] = ['key' => $key];
                        break;
                }
            }
        }

        // Deduplicate countries
        if (! empty($geoLocations['countries'])) {
            $geoLocations['countries'] = array_values(array_unique($geoLocations['countries']));
        }

        $targeting['geo_locations'] = ! empty($geoLocations)
            ? $geoLocations
            : ['countries' => ['IN']]; // default fallback

        // Interests: stored locally as [{id, name}] (from searchInterests()) or, for
        // older rows saved before the interest picker existed, plain free-text strings
        // that Meta can't resolve — those are safely skipped rather than sent as garbage.
        $interests = $adSet['targeting_interests'] ?? [];
        $interestSpec = collect(is_array($interests) ? $interests : [])
            ->filter(fn ($interest) => is_array($interest) && ! empty($interest['id']))
            ->map(fn ($interest) => ['id' => $interest['id'], 'name' => $interest['name'] ?? ''])
            ->values()
            ->all();

        if ($interestSpec) {
            $targeting['flexible_spec'] = [['interests' => $interestSpec]];
        }

        // Placements: our UI collects fine-grained positions (facebook_feed,
        // instagram_feed, instagram_stories, instagram_reels, messenger). Meta's
        // targeting spec wants a top-level publisher_platforms list plus a separate
        // *_positions array per platform. Leaving placements empty keeps Meta's
        // default "Advantage+ / automatic placements" behavior (unchanged from before).
        $placements = $adSet['placements'] ?? [];
        if (! empty($placements) && is_array($placements)) {
            $positionMap = [
                'facebook_feed'     => ['platform' => 'facebook', 'position' => 'feed', 'key' => 'facebook_positions'],
                'instagram_feed'    => ['platform' => 'instagram', 'position' => 'stream', 'key' => 'instagram_positions'],
                'instagram_stories' => ['platform' => 'instagram', 'position' => 'story', 'key' => 'instagram_positions'],
                'instagram_reels'   => ['platform' => 'instagram', 'position' => 'reels', 'key' => 'instagram_positions'],
                'messenger'         => ['platform' => 'messenger', 'position' => 'messenger_home', 'key' => 'messenger_positions'],
            ];

            $publisherPlatforms = [];
            $positionsByKey = [];

            foreach ($placements as $placement) {
                if (! isset($positionMap[$placement])) {
                    continue;
                }

                $map = $positionMap[$placement];
                $publisherPlatforms[$map['platform']] = true;
                $positionsByKey[$map['key']][$map['position']] = true;
            }

            if ($publisherPlatforms) {
                $targeting['publisher_platforms'] = array_keys($publisherPlatforms);

                foreach ($positionsByKey as $key => $positions) {
                    $targeting[$key] = array_keys($positions);
                }
            }
        }

        return $targeting;
    }
}
