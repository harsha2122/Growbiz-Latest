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

        $this->pageTitle('Enquiries');

        $count = $store ? Enquiry::query()->where('store_id', $store->id)->count() : 0;
        $visible = $store?->enquiryDetailsVisible() ?? false;

        $enquiries = $visible
            ? Enquiry::query()->where('store_id', $store->id)->with('product')->latest()->paginate(20)
            : null;

        return MarketplaceHelper::view('vendor-dashboard.enquiries.index', compact('count', 'visible', 'enquiries'));
    }
}
