<?php

namespace Botble\Marketplace\Http\Requests\Fronts;

use Botble\Support\Http\Requests\Request;

class StoreMetaAdSetRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'campaign_id' => ['required', 'integer', 'exists:meta_campaigns,id'],
            'daily_budget' => ['nullable', 'numeric', 'min:1'],
            'targeting_age_min' => ['nullable', 'integer', 'min:13', 'max:65'],
            'targeting_age_max' => ['nullable', 'integer', 'min:13', 'max:65', 'gte:targeting_age_min'],
            'targeting_genders' => ['nullable', 'string', 'in:all,male,female'],
            'targeting_locations' => ['nullable', 'array'],
            'targeting_interests' => ['nullable', 'array'],
            'placements' => ['nullable', 'array'],
            'optimization_goal' => ['nullable', 'string', 'in:LINK_CLICKS,IMPRESSIONS,REACH,CONVERSIONS'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,PAUSED'],
        ];
    }
}
