<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class SubscriptionPlanRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'max_products' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_default' => [new OnOffRule()],
            'is_active' => [new OnOffRule()],
        ];
    }
}
