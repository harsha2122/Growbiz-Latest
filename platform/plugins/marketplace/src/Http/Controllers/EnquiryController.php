<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\StoreEnquiryVisibility;
use Botble\Marketplace\Tables\AllEnquiriesTable;
use Botble\Marketplace\Tables\EnquiryTable;
use Botble\Marketplace\Tables\EnquiryVendorTable;
use Illuminate\Http\Request;

class EnquiryController extends BaseController
{
    public function index(EnquiryVendorTable $table)
    {
        $this->pageTitle('Enquiries');

        return $table->renderTable();
    }

    public function all(AllEnquiriesTable $table)
    {
        $this->pageTitle('All Enquiries');

        return $table->renderTable();
    }

    public function vendor(int $storeId, EnquiryTable $table)
    {
        $store = Store::query()->findOrFail($storeId);

        $this->pageTitle('Enquiries: ' . $store->name);

        return $table
            ->forStore($storeId)
            ->setView('plugins/marketplace::enquiries.vendor')
            ->renderTable(['store' => $store]);
    }

    public function toggleVisibility(Request $request)
    {
        $request->validate([
            'store_id' => ['required', 'integer', 'exists:mp_stores,id'],
            'visible' => ['required', 'boolean'],
        ]);

        $storeId = (int) $request->input('store_id');
        $visible = $request->boolean('visible');

        StoreEnquiryVisibility::query()->updateOrCreate(
            ['store_id' => $storeId],
            ['details_visible' => $visible]
        );

        return $this->httpResponse()
            ->setData(['visible' => $visible])
            ->setMessage(__('Enquiry visibility updated.'));
    }
}
