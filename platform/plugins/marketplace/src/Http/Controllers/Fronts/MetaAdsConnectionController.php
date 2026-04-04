<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MetaAdsConnectionController extends BaseController
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
        $this->pageTitle(__('Meta Ads — Connection'));

        $adAccount = MetaAdAccount::query()->where('store_id', $this->storeId)->first();
        $appId = MarketplaceHelper::getMetaAdsAuthAppId();
        $redirectUri = MarketplaceHelper::getSetting('meta_ads_fb_auth_redirect_uri', url('/vendor/meta-ads/callback'));

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.connection', compact('adAccount', 'appId', 'redirectUri'));
    }

    public function redirect()
    {
        $appId = MarketplaceHelper::getMetaAdsAuthAppId();
        $redirectUri = MarketplaceHelper::getSetting('meta_ads_fb_auth_redirect_uri', url('/vendor/meta-ads/callback'));

        $scopes = 'ads_management,ads_read,pages_show_list,business_management';

        $url = 'https://www.facebook.com/' . MarketplaceHelper::getMetaAdsApiVersion() . '/dialog/oauth?'
            . http_build_query([
                'client_id' => $appId,
                'redirect_uri' => $redirectUri,
                'scope' => $scopes,
                'response_type' => 'code',
                'state' => csrf_token(),
            ]);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()
                ->route('marketplace.vendor.meta-ads.connection')
                ->with('error', __('Facebook authorization was denied: :reason', ['reason' => $request->input('error_description', 'Unknown')]));
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect()
                ->route('marketplace.vendor.meta-ads.connection')
                ->with('error', __('No authorization code received from Facebook.'));
        }

        $appId = MarketplaceHelper::getMetaAdsAuthAppId();
        $appSecret = MarketplaceHelper::getMetaAdsAuthAppSecret();
        $redirectUri = MarketplaceHelper::getSetting('meta_ads_fb_auth_redirect_uri', url('/vendor/meta-ads/callback'));
        $apiVersion = MarketplaceHelper::getMetaAdsApiVersion();

        // Exchange code for short-lived token
        $tokenResponse = Http::get("https://graph.facebook.com/{$apiVersion}/oauth/access_token", [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($tokenResponse->failed()) {
            return redirect()
                ->route('marketplace.vendor.meta-ads.connection')
                ->with('error', __('Failed to exchange code for token. Please try again.'));
        }

        $shortToken = $tokenResponse->json('access_token');

        // Exchange for long-lived token (60 days)
        $longTokenResponse = Http::get("https://graph.facebook.com/{$apiVersion}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $shortToken,
        ]);

        $accessToken = $longTokenResponse->successful()
            ? $longTokenResponse->json('access_token')
            : $shortToken;

        $expiresIn = $longTokenResponse->successful()
            ? $longTokenResponse->json('expires_in', 5184000)
            : 3600;

        // Get user info
        $userResponse = Http::get("https://graph.facebook.com/{$apiVersion}/me", [
            'access_token' => $accessToken,
            'fields' => 'id,name',
        ]);

        $fbUserId = $userResponse->json('id', '');
        $fbUserName = $userResponse->json('name', '');

        // Get ad accounts
        $adAccountsResponse = Http::get("https://graph.facebook.com/{$apiVersion}/me/adaccounts", [
            'access_token' => $accessToken,
            'fields' => 'id,name,account_status',
        ]);

        $adAccounts = $adAccountsResponse->json('data', []);
        $firstAccount = $adAccounts[0] ?? null;

        MetaAdAccount::query()->updateOrCreate(
            ['store_id' => $this->storeId],
            [
                'fb_user_id' => $fbUserId,
                'fb_user_name' => $fbUserName,
                'ad_account_id' => $firstAccount['id'] ?? null,
                'ad_account_name' => $firstAccount['name'] ?? null,
                'access_token' => $accessToken,
                'token_expires_at' => now()->addSeconds($expiresIn),
                'is_connected' => true,
                'connected_at' => now(),
            ]
        );

        return redirect()
            ->route('marketplace.vendor.meta-ads.connection')
            ->with('success', __('Facebook account connected successfully!'));
    }

    public function disconnect()
    {
        $adAccount = MetaAdAccount::query()->where('store_id', $this->storeId)->first();

        if ($adAccount) {
            $adAccount->update([
                'is_connected' => false,
                'access_token' => null,
                'token_expires_at' => null,
            ]);
        }

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.connection'))
            ->setMessage(__('Facebook account disconnected.'));
    }

    public function updateAdAccount(Request $request)
    {
        $request->validate(['ad_account_id' => 'required|string']);

        $adAccount = MetaAdAccount::query()->where('store_id', $this->storeId)->firstOrFail();
        $apiVersion = MarketplaceHelper::getMetaAdsApiVersion();

        // Fetch ad account name from API
        $response = Http::get("https://graph.facebook.com/{$apiVersion}/{$request->input('ad_account_id')}", [
            'access_token' => $adAccount->access_token,
            'fields' => 'id,name',
        ]);

        $adAccount->update([
            'ad_account_id' => $request->input('ad_account_id'),
            'ad_account_name' => $response->json('name', $request->input('ad_account_id')),
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.connection'))
            ->setMessage(__('Ad account updated.'));
    }
}
