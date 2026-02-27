<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Http\Requests\Fronts\StoreMetaCampaignRequest;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaAdsService;
use Botble\Marketplace\Tables\MetaCampaignTable;

class MetaCampaignController extends BaseController
{
    public function __construct(private MetaAdsService $metaAdsService) {}

    public function index(MetaCampaignTable $table)
    {
        $this->pageTitle(__('Meta Ad Campaigns'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(__('Create Campaign'));

        $store = auth('customer')->user()->store;
        $adAccount = MetaAdAccount::query()
            ->where('store_id', $store?->id)
            ->where('is_connected', true)
            ->first();

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.campaigns.create', compact('adAccount'));
    }

    public function store(StoreMetaCampaignRequest $request)
    {
        $store = auth('customer')->user()->store;

        $adAccount = MetaAdAccount::query()
            ->where('store_id', $store?->id)
            ->where('is_connected', true)
            ->first();

        if (! $adAccount) {
            return $this->httpResponse()
                ->setError()
                ->setNextUrl(route('marketplace.vendor.meta-ads.accounts.index'))
                ->setMessage(__('Please connect a Facebook Ad Account first.'));
        }

        $data = $request->validated();

        $remoteId = $this->metaAdsService->safely(
            fn () => $this->metaAdsService->createCampaign($adAccount, $data)
        );

        MetaCampaign::query()->create([
            'store_id' => $store->id,
            'ad_account_id' => $adAccount->id,
            'meta_remote_id' => $remoteId,
            'name' => $data['name'],
            'objective' => $data['objective'],
            'daily_budget' => $data['daily_budget'] ?? null,
            'lifetime_budget' => $data['lifetime_budget'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? 'PAUSED',
        ]);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->setMessage(__('Campaign created successfully.'));
    }

    public function edit(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $campaign = MetaCampaign::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $this->pageTitle(__('Edit Campaign: :name', ['name' => $campaign->name]));

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.campaigns.edit', compact('campaign'));
    }

    public function update(int|string $id, StoreMetaCampaignRequest $request)
    {
        $store = auth('customer')->user()->store;

        $campaign = MetaCampaign::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $data = $request->validated();
        $newStatus = $data['status'] ?? $campaign->status;

        if ($campaign->meta_remote_id && $newStatus !== $campaign->status) {
            $adAccount = $campaign->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->updateCampaignStatus($adAccount, $campaign->meta_remote_id, $newStatus)
                );
            }
        }

        $campaign->fill([
            'name' => $data['name'],
            'objective' => $data['objective'],
            'daily_budget' => $data['daily_budget'] ?? null,
            'lifetime_budget' => $data['lifetime_budget'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $newStatus,
        ])->save();

        return $this->httpResponse()
            ->setPreviousUrl(route('marketplace.vendor.meta-ads.campaigns.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $campaign = MetaCampaign::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        if ($campaign->meta_remote_id) {
            $adAccount = $campaign->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->deleteCampaign($adAccount, $campaign->meta_remote_id)
                );
            }
        }

        $campaign->delete();

        return $this->httpResponse()
            ->setMessage(__('Campaign deleted successfully.'));
    }

    public function pause(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $campaign = MetaCampaign::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        if ($campaign->meta_remote_id) {
            $adAccount = $campaign->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->updateCampaignStatus($adAccount, $campaign->meta_remote_id, 'PAUSED')
                );
            }
        }

        $campaign->update(['status' => 'PAUSED']);

        return $this->httpResponse()
            ->setMessage(__('Campaign paused successfully.'));
    }

    public function resume(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $campaign = MetaCampaign::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        if ($campaign->meta_remote_id) {
            $adAccount = $campaign->adAccount;
            if ($adAccount?->is_connected) {
                $this->metaAdsService->safely(
                    fn () => $this->metaAdsService->updateCampaignStatus($adAccount, $campaign->meta_remote_id, 'ACTIVE')
                );
            }
        }

        $campaign->update(['status' => 'ACTIVE']);

        return $this->httpResponse()
            ->setMessage(__('Campaign resumed successfully.'));
    }
}
