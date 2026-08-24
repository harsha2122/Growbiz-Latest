<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Models\ProductClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceBookingController extends BaseController
{
    public function book(int|string $product, Request $request): RedirectResponse
    {
        $product = Product::query()->with('store')->findOrFail($product);

        abort_if($product->product_type != ProductTypeEnum::SERVICE, 404);

        $store = $product->store;

        if (! $store || ! $store->whatsapp_number_for_booking) {
            return redirect()->back()->with('error_msg', __('This seller has not set up a booking number yet.'));
        }

        ProductClick::track($product, $request);

        $number = preg_replace('/[^0-9]/', '', $store->whatsapp_number_for_booking);

        $message = rawurlencode(__('Hi, I would like to book :service.', ['service' => $product->name]));

        return redirect()->away("https://wa.me/$number?text=$message");
    }
}
