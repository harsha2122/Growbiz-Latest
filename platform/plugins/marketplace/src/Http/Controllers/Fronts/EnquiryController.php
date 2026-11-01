<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Http\Requests\EnquiryRequest;
use Botble\Marketplace\Models\Enquiry;

class EnquiryController extends BaseController
{
    public function store(EnquiryRequest $request)
    {
        $validated = $request->validated();
        $validated['customer_id'] = auth('customer')->id();
        if (! empty($validated['product_id'])) {
            $enquiryProduct = \Botble\Ecommerce\Models\Product::query()->find($validated['product_id']);
            abort_unless($enquiryProduct?->original_product?->store_id == $validated['store_id'], 422);
        }

        Enquiry::query()->create($validated);

        return $this->httpResponse()->setMessage(__('Thank you! Your enquiry has been sent to the vendor.'));
    }
}
