<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Contact\Models\Contact;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;

class ContactAdminController extends BaseController
{
    public function index()
    {
        SeoHelper::setTitle(__('Contact Admin'));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Contact Admin'));

        return Theme::scope(
            'ecommerce.customers.contact-admin',
            [],
            'plugins/ecommerce::themes.customers.contact-admin'
        )->render();
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        $customer = auth('customer')->user();

        Contact::query()->create([
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone ?? '',
            'subject' => $request->input('subject'),
            'content' => $request->input('content'),
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('customer.contact-admin'))
            ->setMessage(__('Your message has been sent successfully. We will get back to you soon.'));
    }
}
