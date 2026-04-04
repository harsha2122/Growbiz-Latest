<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Http\Request;

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
            $oauthUrl = 'https://www.facebook.com/dialog/oauth?' . http_build_query([
                'client_id'     => $authAppId,
                'redirect_uri'  => $redirectUri,
                'scope'         => 'ads_management,ads_read,business_management,pages_show_list',
                'response_type' => 'code',
            ]);
        }

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.connection', compact('adAccount', 'oauthUrl'));
    }

    public function callback(Request $request, MetaApiClient $metaClient)
    {
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
                ->with('error', 'Could not fetch Facebook user info.');
        }

        // Step 4: fetch ad accounts + pages
        $adAccounts = $metaClient->getAdAccounts($accessToken);
        $pages      = $metaClient->getPages($accessToken);

        // Store in session for account selection step
        session([
            'meta_oauth' => [
                'access_token' => $accessToken,
                'expires_in'   => $expiresIn,
                'fb_user_id'   => $me['id'],
                'fb_user_name' => $me['name'] ?? '',
                'ad_accounts'  => $adAccounts,
                'pages'        => $pages,
            ],
        ]);

        // Auto-save if only one choice available
        if (count($adAccounts) === 1 && count($pages) <= 1) {
            return $this->saveAccountSelection($accessToken, $expiresIn, $me, $adAccounts[0], $pages[0] ?? null);
        }

        $this->pageTitle('Select Ad Account');

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.select-account', [
            'adAccounts' => $adAccounts,
            'pages'      => $pages,
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

        $selectedAccount = collect($oauthData['ad_accounts'])->firstWhere('id', $request->input('ad_account_id'));

        if (! $selectedAccount) {
            return back()->with('error', 'Selected ad account not found.');
        }

        $selectedPage = $request->input('fb_page_id')
            ? collect($oauthData['pages'])->firstWhere('id', $request->input('fb_page_id'))
            : null;

        $me = ['id' => $oauthData['fb_user_id'], 'name' => $oauthData['fb_user_name']];

        session()->forget('meta_oauth');

        return $this->saveAccountSelection($oauthData['access_token'], $oauthData['expires_in'], $me, $selectedAccount, $selectedPage);
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

    private function saveAccountSelection(string $accessToken, ?int $expiresIn, array $me, array $adAccount, ?array $page)
    {
        $rawAccountId = ltrim($adAccount['id'] ?? '', 'act_');

        MetaAdAccount::query()->updateOrCreate(
            ['store_id' => $this->storeId],
            [
                'access_token'     => $accessToken,
                'token_expires_at' => $expiresIn ? now()->addSeconds($expiresIn) : now()->addDays(60),
                'is_connected'     => true,
                'connected_at'     => now(),
                'fb_user_id'       => $me['id'],
                'fb_user_name'     => $me['name'] ?? '',
                'ad_account_id'    => $rawAccountId,
                'ad_account_name'  => $adAccount['name'] ?? '',
                'fb_page_id'       => $page['id'] ?? null,
                'fb_page_name'     => $page['name'] ?? null,
            ]
        );

        return redirect()->route('marketplace.vendor.meta-ads.connection')
            ->with('success', 'Facebook account connected successfully!');
    }
}
