<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Marketplace\Enums\VendorPaymentStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class VendorPaymentReviewRequest extends Request
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([VendorPaymentStatusEnum::APPROVED, VendorPaymentStatusEnum::DECLINED])],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
