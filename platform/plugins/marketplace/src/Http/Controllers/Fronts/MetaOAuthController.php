<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Services\MetaAdsService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaOAuthController extends BaseController
{
    public function __construct(private MetaAdsService $metaAdsService) {}

    /**
     * Redirect the vendor to Facebook's OAuth dialog.
     */
    public function redirect(): RedirectResponse
    {
        $redirectUri = route('marketplace.vendor.meta-ads.oauth.callback');
        $url = $this->metaAdsService->getAuthUrl($redirectUri);

        return redirect()->away($url);
    }

    /**
     * Handle the OAuth callback from Facebook.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()
                ->route('marketplace.vendor.meta-ads.accounts.index')
                ->with('error', $request->input('error_description', __('Facebook authorization was denied.')));
        }

        $code = $request->input('code');

        if (! $code) {
            return redirect()
                ->route('marketplace.vendor.meta-ads.accounts.index')
                ->with('error', __('No authorization code received from Facebook.'));
        }

        try {
            $redirectUri = route('marketplace.vendor.meta-ads.oauth.callback');
            $tokenData = $this->metaAdsService->exchangeCodeForToken($code, $redirectUri);

            $accessToken = $tokenData['access_token'] ?? null;
            $expiresIn = $tokenData['expires_in'] ?? null;

            if (! $accessToken) {
                throw new Exception('No access token in response.');
            }

            // Fetch user profile
            $profile = $this->metaAdsService->getUserProfile($accessToken);

            // Fetch connected ad accounts and pick the first active one
            $adAccounts = $this->metaAdsService->getUserAdAccounts($accessToken);
            $firstAccount = collect($adAccounts)->first(fn ($a) => ($a['account_status'] ?? 0) === 1);

            $store = auth('customer')->user()->store;

            MetaAdAccount::query()->updateOrCreate(
                ['store_id' => $store->id],
                [
                    'fb_user_id' => $profile['id'] ?? null,
                    'fb_user_name' => $profile['name'] ?? null,
                    'ad_account_id' => $firstAccount['id'] ?? null,
                    'ad_account_name' => $firstAccount['name'] ?? null,
                    'access_token' => $accessToken,
                    'token_expires_at' => $expiresIn ? now()->addSeconds($expiresIn) : null,
                    'is_connected' => true,
                    'connected_at' => now(),
                ]
            );

            return redirect()
                ->route('marketplace.vendor.meta-ads.accounts.index')
                ->with('success', __('Facebook Ad Account connected successfully.'));
        } catch (Exception $e) {
            Log::error('Meta OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()
                ->route('marketplace.vendor.meta-ads.accounts.index')
                ->with('error', __('Failed to connect Facebook account. Please try again.'));
        }
    }
}
