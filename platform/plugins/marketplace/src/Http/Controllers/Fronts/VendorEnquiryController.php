<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Enquiry;

class VendorEnquiryController extends BaseController
{
    public function index()
    {
        $store = auth('customer')->user()?->store;
        abort_unless($store?->exists, 404);

        $this->pageTitle('Enquiries');

        $query = Enquiry::query()->where('store_id', $store->id);
        $through = (int) (clone $query)->max('id');
        $count = (clone $query)->where('id', '<=', $through)->count();
        $newCount = (clone $query)->where('id', '<=', $through)->whereNull('seller_viewed_at')->count();
        $viewedNow = 0;
        $visible = $store?->enquiryDetailsVisible() ?? false;

        if ($visible) {
            $viewedNow = app(\Botble\Marketplace\Services\ActivityNotificationService::class)->viewEnquiries($store->id, $through);
            $newCount = 0;
        }

        $enquiries = $visible
            ? Enquiry::query()->where('store_id', $store->id)->where('id', '<=', $through)->with('product')->latest()->paginate(20)
            : null;

        return MarketplaceHelper::view('vendor-dashboard.enquiries.index', compact('count', 'visible', 'enquiries', 'newCount', 'viewedNow'));
    }
}
