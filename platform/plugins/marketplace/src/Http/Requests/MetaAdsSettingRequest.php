<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class MetaAdsSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'meta_ads_enabled' => [new OnOffRule()],
            'meta_app_id' => ['nullable', 'string', 'max:255'],
            'meta_app_secret' => ['nullable', 'string', 'max:255'],
            'meta_api_version' => ['nullable', 'string', 'max:20'],
            'meta_ads_min_daily_budget' => ['nullable', 'numeric', 'min:0'],
            'meta_ads_default_currency' => ['nullable', 'string', 'max:10'],
        ];
    }
}
