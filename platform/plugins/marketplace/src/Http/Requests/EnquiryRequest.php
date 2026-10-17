<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class EnquiryRequest extends Request
{
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:mp_stores,id'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'source' => ['required', 'in:vendor_page,product_page'],
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'digits:10'],
            'email' => ['required', 'email:rfc,strict', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
        ];
    }
}
