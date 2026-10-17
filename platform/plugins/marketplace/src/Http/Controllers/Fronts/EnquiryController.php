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

        Enquiry::query()->create($validated);

        return $this->httpResponse()->setMessage(__('Thank you! Your enquiry has been sent to the vendor.'));
    }
}
