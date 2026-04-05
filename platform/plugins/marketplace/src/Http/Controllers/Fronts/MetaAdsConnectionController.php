<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MetaAdsConnectionController extends BaseController
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
        $this->pageTitle('Facebook Connection');

        $adAccount = MetaAdAccount::query()->where('store_id', $this->storeId)->first();

        $authAppId   = MarketplaceHelper::getMetaAdsAuthAppId();
        $redirectUri = route('marketplace.vendor.meta-ads.callback');

        $oauthUrl = null;
        if ($authAppId) {
            // FIX #1: Add state parameter (CSRF protection)
            $state = Str::random(40);
            session(['meta_oauth_state' => $state]);

            // FIX #7: Minimal required scopes only
            $oauthUrl = 'https://www.facebook.com/dialog/oauth?' . http_build_query([
                'client_id'     => $authAppId,
                'redirect_uri'  => $redirectUri,
                'scope'         => 'ads_management,ads_read',
                'response_type' => 'code',
                'state'         => $state,
            ]);
        }

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.connection', compact('adAccount', 'oauthUrl'));
    }

    public function callback(Request $request)
    {
        // FIX #1: Verify state parameter against CSRF attack
        $state         = $request->input('state');
        $expectedState = session('meta_oauth_state');
        session()->forget('meta_oauth_state');

        if (! $state || ! $expectedState || ! hash_equals($expectedState, $state)) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Invalid state parameter. Authorization request may have been tampered with.');
        }

        if ($request->has('error')) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Facebook authorization failed: ' . $request->input('error_description', 'Unknown error.'));
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'No authorization code received from Facebook.');
        }

        $appId       = MarketplaceHelper::getMetaAdsAuthAppId();
        $appSecret   = MarketplaceHelper::getMetaAdsAuthAppSecret();
        $redirectUri = route('marketplace.vendor.meta-ads.callback');

        if (! $appId || ! $appSecret) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Facebook App credentials are not configured. Contact admin.');
        }

        // Step 1: exchange code for short-lived token
        $metaClient = app(MetaApiClient::class);
        $tokenData = $metaClient->exchangeCodeForToken($code, $appId, $appSecret, $redirectUri);

        if (empty($tokenData['access_token'])) {
            $msg = $tokenData['error']['message'] ?? 'Failed to get access token from Facebook.';

            return redirect()->route('marketplace.vendor.meta-ads.connection')->with('error', $msg);
        }

        $shortToken = $tokenData['access_token'];

        // Step 2: extend to long-lived token (~60 days)
        $longTokenData = $metaClient->extendToken($shortToken, $appId, $appSecret);
        $accessToken   = $longTokenData['access_token'] ?? $shortToken;
        $expiresIn     = $longTokenData['expires_in'] ?? ($tokenData['expires_in'] ?? null);

        // Step 3: fetch user info
        $me = $metaClient->getMe($accessToken);
        if (empty($me['id'])) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Could not fetch Facebook user info. Token may be invalid.');
        }

        // FIX #2: Verify token by fetching ad accounts (validates token is working)
        $adAccounts = $metaClient->getAdAccounts($accessToken);

        if (empty($adAccounts)) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'No ad accounts found or token is invalid. Make sure your Facebook account has access to at least one ad account.');
        }

        // Store in session for account selection step
        session([
            'meta_oauth' => [
                'access_token' => $accessToken,
                'expires_in'   => $expiresIn,
                'fb_user_id'   => $me['id'],
                'fb_user_name' => $me['name'] ?? '',
                'ad_accounts'  => $adAccounts,
            ],
        ]);

        // FIX #4: Always show selection page (even for single account) so user confirms
        $this->pageTitle('Select Ad Account');

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.select-account', [
            'adAccounts' => $adAccounts,
        ]);
    }

    public function selectAccount(Request $request)
    {
        $oauthData = session('meta_oauth');

        if (! $oauthData) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Session expired. Please reconnect.');
        }

        $request->validate(['ad_account_id' => ['required', 'string']]);

        $selectedAccountId = $request->input('ad_account_id');

        // FIX #6: Validate selected account actually belongs to this user
        $selectedAccount = collect($oauthData['ad_accounts'])->firstWhere('id', $selectedAccountId);

        if (! $selectedAccount) {
            return back()->with('error', 'Selected ad account not found in your Facebook account.');
        }

        $me = ['id' => $oauthData['fb_user_id'], 'name' => $oauthData['fb_user_name']];

        session()->forget('meta_oauth');

        return $this->saveAccountSelection(
            $oauthData['access_token'],
            $oauthData['expires_in'],
            $me,
            $selectedAccount
        );
    }

    public function disconnect()
    {
        MetaAdAccount::query()->where('store_id', $this->storeId)->update([
            'is_connected'     => false,
            'access_token'     => null,
            'fb_user_id'       => null,
            'fb_user_name'     => null,
            'ad_account_id'    => null,
            'ad_account_name'  => null,
            'fb_page_id'       => null,
            'fb_page_name'     => null,
            'token_expires_at' => null,
            'connected_at'     => null,
        ]);

        return redirect()->route('marketplace.vendor.meta-ads.connection')
            ->with('success', 'Facebook account disconnected.');
    }

    private function saveAccountSelection(string $accessToken, ?int $expiresIn, array $me, array $adAccount)
    {
        // Strip "act_" prefix stored separately in ad_account_id
        $rawAccountId = ltrim($adAccount['id'] ?? '', 'act_');

        MetaAdAccount::query()->updateOrCreate(
            ['store_id' => $this->storeId],
            [
                'access_token'     => $accessToken,
                // FIX #2 & #3: Store exact expiry for refresh tracking
                'token_expires_at' => $expiresIn ? now()->addSeconds($expiresIn) : now()->addDays(60),
                'is_connected'     => true,
                'connected_at'     => now(),
                'fb_user_id'       => $me['id'],
                'fb_user_name'     => $me['name'] ?? '',
                'ad_account_id'    => $rawAccountId,
                'ad_account_name'  => $adAccount['name'] ?? '',
                // FIX #5: page_id is optional — only set if provided
                'fb_page_id'       => null,
                'fb_page_name'     => null,
            ]
        );

        return redirect()->route('marketplace.vendor.meta-ads.connection')
            ->with('success', 'Facebook account connected successfully!');
    }
}
