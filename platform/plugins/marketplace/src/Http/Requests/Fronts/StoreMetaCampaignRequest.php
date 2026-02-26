<?php

namespace Botble\Marketplace\Http\Requests\Fronts;

use Botble\Support\Http\Requests\Request;

class StoreMetaCampaignRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string', 'in:TRAFFIC,CONVERSIONS,BRAND_AWARENESS,REACH,ENGAGEMENT,LEAD_GENERATION'],
            'daily_budget' => ['nullable', 'numeric', 'min:1'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,PAUSED'],
        ];
    }
}
