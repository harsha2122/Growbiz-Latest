<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Rules\MediaImageRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ServiceRequest extends Request
{
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:mp_stores,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string', new MediaImageRule()],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => Rule::in(BaseStatusEnum::values()),
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
