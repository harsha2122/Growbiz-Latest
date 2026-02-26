<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\MetaAdAccount;

class MetaAdAccountController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Meta Ad Accounts'));

        $store = auth('customer')->user()->store;
        $account = MetaAdAccount::query()
            ->where('store_id', $store?->id)
            ->first();

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.accounts.index', compact('account', 'store'));
    }

    public function connect()
    {
        $this->pageTitle(__('Connect Facebook Account'));

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.accounts.connect');
    }

    public function disconnect(int|string $id)
    {
        $store = auth('customer')->user()->store;

        $account = MetaAdAccount::query()
            ->where('id', $id)
            ->where('store_id', $store?->id)
            ->firstOrFail();

        $account->update([
            'is_connected' => false,
            'access_token' => null,
            'token_expires_at' => null,
        ]);

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.vendor.meta-ads.accounts.index'))
            ->setMessage(__('Facebook account disconnected successfully.'));
    }
}
