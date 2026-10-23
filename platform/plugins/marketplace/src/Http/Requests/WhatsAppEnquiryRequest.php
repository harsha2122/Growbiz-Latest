<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WhatsAppEnquiryRequest extends Request
{
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:mp_stores,id'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'digits:10'],
        ];
    }
}
