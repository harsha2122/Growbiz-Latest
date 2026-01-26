<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Contact\Models\Contact;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;

class ContactAdminController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Contact Admin'));

        return MarketplaceHelper::view('vendor-dashboard.contact-admin');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        $customer = auth('customer')->user();
        $store = $customer->store;

        $storeName = $store ? ' (Store: ' . $store->name . ')' : '';

        Contact::query()->create([
            'name' => $customer->name . $storeName,
            'email' => $customer->email,
            'phone' => $customer->phone ?? '',
            'subject' => '[Vendor] ' . $request->input('subject'),
            'content' => $request->input('content'),
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.contact-admin'))
            ->setMessage(__('Your message has been sent successfully. We will get back to you soon.'));
    }
}
