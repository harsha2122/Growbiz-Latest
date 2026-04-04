<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;

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

        $authAppId = MarketplaceHelper::getMetaAdsAuthAppId();
        $redirectUri = url('/vendor/meta-ads/callback');

        $oauthUrl = null;
        if ($authAppId) {
            $oauthUrl = 'https://www.facebook.com/v21.0/dialog/oauth?' . http_build_query([
                'client_id'    => $authAppId,
                'redirect_uri' => $redirectUri,
                'scope'        => 'ads_management,ads_read,business_management',
                'response_type' => 'code',
            ]);
        }

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.connection', compact('adAccount', 'oauthUrl'));
    }

    public function callback()
    {
        $code = request('code');
        $error = request('error');

        if ($error || ! $code) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Facebook authorization failed or was cancelled.');
        }

        $appId     = MarketplaceHelper::getMetaAdsAuthAppId();
        $appSecret = MarketplaceHelper::getMetaAdsAuthAppSecret();
        $redirectUri = url('/vendor/meta-ads/callback');

        if (! $appId || ! $appSecret) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Facebook App credentials are not configured. Contact admin.');
        }

        $tokenUrl = 'https://graph.facebook.com/v21.0/oauth/access_token?' . http_build_query([
            'client_id'     => $appId,
            'client_secret' => $appSecret,
            'redirect_uri'  => $redirectUri,
            'code'          => $code,
        ]);

        $response = @file_get_contents($tokenUrl);
        $data = $response ? json_decode($response, true) : [];

        if (empty($data['access_token'])) {
            return redirect()->route('marketplace.vendor.meta-ads.connection')
                ->with('error', 'Failed to get access token from Facebook.');
        }

        MetaAdAccount::query()->updateOrCreate(
            ['store_id' => $this->storeId],
            [
                'access_token' => $data['access_token'],
                'token_expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
                'is_connected' => true,
                'connected_at' => now(),
            ]
        );

        return redirect()->route('marketplace.vendor.meta-ads.connection')
            ->with('success', 'Facebook account connected successfully!');
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
            'token_expires_at' => null,
            'connected_at'     => null,
        ]);

        return redirect()->route('marketplace.vendor.meta-ads.connection')
            ->with('success', 'Facebook account disconnected.');
    }
}
