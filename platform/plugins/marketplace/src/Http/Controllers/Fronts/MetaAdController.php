<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Services\MetaApiClient;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaAdController extends BaseController
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

    public function create(int $adSetId)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->with('campaign')->findOrFail($adSetId);

        $this->pageTitle('Create Ad');

        $products = Product::query()
            ->where('store_id', $this->storeId)
            ->where('is_variation', 0)
            ->wherePublished()
            ->select('id', 'name', 'image', 'price')
            ->get();

        $adAccount = $this->getConnectedAccount();
        $hasPage   = $adAccount && ! empty($adAccount->fb_page_id);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.create', compact('adSet', 'products', 'hasPage'));
    }

    public function store(Request $request, int $adSetId)
    {
        $adSet = MetaAdSet::query()->where('store_id', $this->storeId)->findOrFail($adSetId);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'format'          => ['required', 'in:SINGLE_IMAGE,CAROUSEL,VIDEO'],
            'primary_text'    => ['required', 'string', 'max:500'],
            'headline'        => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'cta_button'      => ['required', 'in:LEARN_MORE,SHOP_NOW,SIGN_UP,BOOK_NOW,CONTACT_US,DOWNLOAD,GET_OFFER'],
            'destination_url' => ['required', 'url', 'max:500'],
            'creative_file'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi', 'max:102400'],
            'product_id'      => ['nullable', 'integer'],
        ]);

        // Handle file upload
        $imageUrl = null;
        if ($request->hasFile('creative_file') && $request->file('creative_file')->isValid()) {
            $customer     = auth('customer')->user();
            $uploadFolder = $customer->upload_folder ?? 'meta-ads';
            $result       = RvMedia::handleUpload($request->file('creative_file'), 0, $uploadFolder);

            if (! $result['error']) {
                $imageUrl = $result['data']->url;
            } else {
                return back()->withErrors(['creative_file' => 'Upload failed: ' . $result['message']])->withInput();
            }
        }

        $validated['ad_set_id']   = $adSet->id;
        $validated['campaign_id'] = $adSet->campaign_id;
        $validated['store_id']    = $this->storeId;
        $validated['status']      = 'IN_REVIEW';
        $validated['image_url']   = $imageUrl;
        unset($validated['creative_file']);

        $ad = MetaAd::query()->create($validated);

        // Push to Meta API
        if ($adSet->meta_adset_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount && empty($adAccount->fb_page_id)) {
                Log::warning('Meta ad creation: no Facebook Page linked for store', ['store_id' => $this->storeId]);
            } elseif ($adAccount) {
                $pushResult = $this->syncAdToMeta($ad, $adSet, $adAccount);
                if (! $pushResult['success']) {
                    Log::warning('Meta ad push failed on store', ['store_id' => $this->storeId, 'error' => $pushResult['error']]);
                }
            }
        } else {
            Log::warning('Meta ad creation skipped: ad set not synced to Meta', [
                'ad_set_id' => $adSet->id,
                'store_id'  => $this->storeId,
            ]);
        }

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id))
            ->withCreatedSuccessMessage();
    }

    public function show(int $id)
    {
        $ad = MetaAd::query()
            ->where('store_id', $this->storeId)
            ->with(['adSet.campaign', 'product'])
            ->findOrFail($id);

        $this->pageTitle($ad->name);

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.show', compact('ad'));
    }

    public function edit(int $id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->with('adSet')->findOrFail($id);

        $this->pageTitle('Edit: ' . $ad->name);

        $products = Product::query()
            ->where('store_id', $this->storeId)
            ->where('is_variation', 0)
            ->wherePublished()
            ->select('id', 'name', 'image', 'price')
            ->get();

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.edit', compact('ad', 'products'));
    }

    public function update(Request $request, int $id)
    {
        $ad = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'format'          => ['required', 'in:SINGLE_IMAGE,CAROUSEL,VIDEO'],
            'primary_text'    => ['required', 'string', 'max:500'],
            'headline'        => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'cta_button'      => ['required', 'in:LEARN_MORE,SHOP_NOW,SIGN_UP,BOOK_NOW,CONTACT_US,DOWNLOAD,GET_OFFER'],
            'destination_url' => ['required', 'url', 'max:500'],
            'creative_file'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi', 'max:102400'],
            'product_id'      => ['nullable', 'integer'],
        ]);

        // Handle file upload on edit
        if ($request->hasFile('creative_file') && $request->file('creative_file')->isValid()) {
            $customer     = auth('customer')->user();
            $uploadFolder = $customer->upload_folder ?? 'meta-ads';
            $result       = RvMedia::handleUpload($request->file('creative_file'), 0, $uploadFolder);

            if (! $result['error']) {
                $validated['image_url'] = $result['data']->url;
            } else {
                return back()->withErrors(['creative_file' => 'Upload failed: ' . $result['message']])->withInput();
            }
        }

        unset($validated['creative_file']);
        $ad->update($validated);

        if ($ad->meta_ad_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->updateAd($adAccount->access_token, $ad->meta_ad_id, [
                        'status' => $ad->status,
                        'name'   => $ad->name,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Meta ad update API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int $id)
    {
        $ad      = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);
        $adSetId = $ad->ad_set_id;

        if ($ad->meta_ad_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->deleteAd($adAccount->access_token, $ad->meta_ad_id);
                } catch (\Throwable $e) {
                    Log::error('Meta ad delete API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $ad->delete();

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.ad-sets.show', $adSetId))
            ->setMessage('Ad deleted.');
    }

    public function toggleStatus(int $id)
    {
        $ad        = MetaAd::query()->where('store_id', $this->storeId)->findOrFail($id);
        $newStatus = $ad->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';
        $ad->update(['status' => $newStatus]);

        if ($ad->meta_ad_id) {
            $adAccount = $this->getConnectedAccount();
            if ($adAccount) {
                try {
                    app(MetaApiClient::class)->updateAd($adAccount->access_token, $ad->meta_ad_id, [
                        'status' => $newStatus,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Meta ad toggleStatus API failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $this->httpResponse()->setMessage('Ad status updated.');
    }

    public function preview(int $id)
    {
        $ad = MetaAd::query()
            ->where('store_id', $this->storeId)
            ->with(['adSet.campaign', 'product'])
            ->findOrFail($id);

        $storeName = auth('customer')->user()->store?->name ?? 'Your Store';

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.ads.preview', compact('ad', 'storeName'));
    }

    /**
     * Re-push an existing ad (with meta_ad_id = NULL) to Meta Ads Manager.
     */
    public function pushToMeta(int $id)
    {
        $ad    = MetaAd::query()->where('store_id', $this->storeId)->with('adSet')->findOrFail($id);
        $adSet = $ad->adSet;

        if (! $adSet || ! $adSet->meta_adset_id) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('Ad set is not synced to Meta yet. Please push the ad set to Meta first, then retry.');
        }

        $adAccount = $this->getConnectedAccount();
        if (! $adAccount) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('No connected Meta ad account found. Please reconnect your Facebook account.');
        }

        if (empty($adAccount->fb_page_id)) {
            return $this->httpResponse()
                ->setError()
                ->setMessage('No Facebook Page linked. Please reconnect and select a Facebook Page.');
        }

        $pushResult = $this->syncAdToMeta($ad, $adSet, $adAccount);

        if ($pushResult['success']) {
            return $this->httpResponse()->setMessage('Ad pushed to Meta successfully! Ad ID: ' . $pushResult['meta_ad_id']);
        }

        return $this->httpResponse()
            ->setError()
            ->setMessage('Failed to push to Meta: ' . $pushResult['error']);
    }

    /**
     * Internal helper — creates the ad creative + ad on Meta and saves returned IDs.
     * Returns ['success' => bool, 'meta_ad_id' => string|null, 'error' => string|null]
     */
    private function syncAdToMeta(MetaAd $ad, MetaAdSet $adSet, MetaAdAccount $adAccount): array
    {
        try {
            $metaClient = app(MetaApiClient::class);

            // Resolve creative image URL: uploaded file → product image
            $creativeUrl = $ad->image_url;
            if (! $creativeUrl && $ad->product_id) {
                $product     = Product::find($ad->product_id);
                $creativeUrl = $product?->image ? RvMedia::getImageUrl($product->image) : null;
            }

            if (! $creativeUrl) {
                return [
                    'success'    => false,
                    'meta_ad_id' => null,
                    'error'      => 'No creative image available. Please edit the ad and upload an image.',
                ];
            }

            $creativeResult = $metaClient->createAdCreative(
                $adAccount->access_token,
                $adAccount->ad_account_id,
                [
                    'name'              => $ad->name . ' Creative',
                    'object_story_spec' => [
                        'page_id'   => $adAccount->fb_page_id,
                        'link_data' => [
                            'link'           => $ad->destination_url,
                            'message'        => $ad->primary_text,
                            'name'           => $ad->headline,
                            'description'    => $ad->description ?? '',
                            'image_url'      => $creativeUrl,
                            'call_to_action' => [
                                'type'  => $ad->cta_button,
                                'value' => ['link' => $ad->destination_url],
                            ],
                        ],
                    ],
                ]
            );

            if (! empty($creativeResult['error'])) {
                $errorMsg = $creativeResult['error']['message']
                    ?? $creativeResult['error']['error_user_title']
                    ?? 'Creative creation failed';
                Log::warning('Meta ad creative create API error', ['error' => $creativeResult['error'], 'ad_id' => $ad->id]);
                return ['success' => false, 'meta_ad_id' => null, 'error' => $errorMsg];
            }

            if (empty($creativeResult['id'])) {
                return ['success' => false, 'meta_ad_id' => null, 'error' => 'Creative creation returned no ID'];
            }

            $adResult = $metaClient->createAd($adAccount->access_token, $adAccount->ad_account_id, [
                'name'     => $ad->name,
                'adset_id' => $adSet->meta_adset_id,
                'creative' => ['creative_id' => $creativeResult['id']],
                'status'   => 'PAUSED',
            ]);

            if (! empty($adResult['error'])) {
                $errorMsg = $adResult['error']['message']
                    ?? $adResult['error']['error_user_title']
                    ?? 'Ad creation failed';
                Log::warning('Meta ad create API error', ['error' => $adResult['error'], 'ad_id' => $ad->id]);
                return ['success' => false, 'meta_ad_id' => null, 'error' => $errorMsg];
            }

            if (! empty($adResult['id'])) {
                $ad->update([
                    'meta_ad_id'       => $adResult['id'],
                    'meta_creative_id' => $creativeResult['id'],
                    'status'           => 'PAUSED',
                ]);
                return ['success' => true, 'meta_ad_id' => $adResult['id'], 'error' => null];
            }

            return ['success' => false, 'meta_ad_id' => null, 'error' => 'Ad creation returned no ID'];
        } catch (\Throwable $e) {
            Log::error('Meta ad push failed', ['error' => $e->getMessage(), 'ad_id' => $ad->id]);
            return ['success' => false, 'meta_ad_id' => null, 'error' => $e->getMessage()];
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
