<?php

namespace Botble\Marketplace\Http\Requests\Fronts;

use Botble\Base\Rules\MediaImageRule;
use Botble\Marketplace\Http\Requests\StoreRequest;

class VendorStoreRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['customer_id'], $rules['status'], $rules['name'], $rules['phone'], $rules['email']);

        $rules['logo_input'] = ['nullable', new MediaImageRule()];
        $rules['cover_image_input'] = ['nullable', new MediaImageRule()];
        $rules['service_tags_input'] = ['nullable', 'string', 'max:1000'];

        return $rules;
    }
}
