<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Http\Requests\WhatsAppEnquiryRequest;
use Botble\Marketplace\Models\Enquiry;
use Botble\Marketplace\Models\ProductClick;

class WhatsAppEnquiryController extends BaseController
{
    public function store(WhatsAppEnquiryRequest $request)
    {
        $validated = $request->validated();

        Enquiry::query()->create([
            ...$validated,
            'source' => 'whatsapp',
        ]);

        $product = ! empty($validated['product_id'])
            ? Product::query()->with('store')->find($validated['product_id'])
            : null;

        $redirectUrl = $this->buildWhatsAppUrl($product);

        $message = $redirectUrl
            ? __('Thank you! Redirecting you to WhatsApp...')
            : __('Thank you! Your details have been saved. The seller has not set up a WhatsApp number yet.');

        return $this
            ->httpResponse()
            ->setData(['redirect_url' => $redirectUrl])
            ->setMessage($message);
    }

    protected function buildWhatsAppUrl(?Product $product): ?string
    {
        if (! $product || $product->product_type != ProductTypeEnum::SERVICE) {
            return null;
        }

        $store = $product->store;

        if (! $store || ! $store->whatsapp_number_for_booking) {
            return null;
        }

        ProductClick::track($product, request());

        $number = preg_replace('/[^0-9]/', '', $store->whatsapp_number_for_booking);

        $serviceName = trim($product->name . ' ' . $product->variation_attributes);

        $message = rawurlencode(__('Hi, I would like to book :service.', ['service' => $serviceName]));

        return "https://wa.me/$number?text=$message";
    }
}
