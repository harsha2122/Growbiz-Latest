<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\Service;
use Botble\Marketplace\Models\ServiceClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicServiceController extends BaseController
{
    public function book(int|string $service, Request $request): RedirectResponse
    {
        $service = Service::query()->with('store')->findOrFail($service);

        $store = $service->store;

        if (! $store || ! $store->whatsapp_number_for_booking) {
            return redirect()->back()->with('error_msg', __('This vendor has not set up a booking number yet.'));
        }

        ServiceClick::track($service, $request);

        $number = preg_replace('/[^0-9]/', '', $store->whatsapp_number_for_booking);

        $message = rawurlencode(__('Hi, I would like to book :service.', ['service' => $service->name]));

        return redirect()->away("https://wa.me/$number?text=$message");
    }
}
