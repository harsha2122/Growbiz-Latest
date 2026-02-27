<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Tables\AdminMetaCampaignTable;

class MetaAdsController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(__('Meta Ads'), route('marketplace.meta-ads.index'));
    }

    public function index(AdminMetaCampaignTable $table)
    {
        $this->pageTitle(__('Meta Ads – Campaigns Overview'));

        return $table->renderTable();
    }

    public function accounts()
    {
        $this->pageTitle(__('Meta Ads – Connected Accounts'));

        $accounts = MetaAdAccount::query()
            ->with('store:id,name')
            ->orderByDesc('connected_at')
            ->paginate(25);

        return view('plugins/marketplace::meta-ads.accounts', compact('accounts'));
    }

    public function summary()
    {
        $this->pageTitle(__('Meta Ads – Summary'));

        $totalCampaigns = MetaCampaign::query()->count();
        $activeCampaigns = MetaCampaign::query()->where('status', 'ACTIVE')->count();
        $totalImpressions = MetaCampaign::query()->sum('impressions');
        $totalClicks = MetaCampaign::query()->sum('clicks');
        $totalSpend = MetaCampaign::query()->sum('spend');
        $connectedAccounts = MetaAdAccount::query()->where('is_connected', true)->count();

        return view('plugins/marketplace::meta-ads.summary', compact(
            'totalCampaigns',
            'activeCampaigns',
            'totalImpressions',
            'totalClicks',
            'totalSpend',
            'connectedAccounts',
        ));
    }
}
