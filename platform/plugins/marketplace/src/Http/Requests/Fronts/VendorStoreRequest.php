<?php

namespace Botble\Marketplace\Http\Requests\Fronts;

use Botble\Base\Rules\MediaImageRule;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Illuminate\Validation\Rule;

class VendorStoreRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['customer_id'], $rules['status'], $rules['name'], $rules['phone'], $rules['email']);

        $rules['logo_input'] = ['nullable', new MediaImageRule()];
        $rules['cover_image_input'] = ['nullable', new MediaImageRule()];
        $rules['service_tags_input'] = ['nullable', 'string', 'max:1000'];
        $rules['service_tags'] = ['prohibited'];

        foreach (['completed_projects', 'years_of_experience', 'happy_customers', 'public_location', 'service_tags_input'] as $field) {
            $rules[$field][] = Rule::prohibitedIf(! $this->user('customer')?->store?->is_minimal_profile);
        }

        return $rules;
    }
}
