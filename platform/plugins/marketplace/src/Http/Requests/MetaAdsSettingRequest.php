<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class MetaAdsSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'meta_ads_enabled'                    => [new OnOffRule()],
            'meta_ads_fb_auth_app_id'             => ['nullable', 'string', 'max:255'],
            'meta_ads_fb_auth_app_secret'         => ['nullable', 'string', 'max:255'],
            'meta_ads_fb_auth_redirect_uri'       => ['nullable', 'string', 'max:500'],
            'meta_ads_marketing_app_id'           => ['nullable', 'string', 'max:255'],
            'meta_ads_marketing_app_secret'       => ['nullable', 'string', 'max:255'],
            'meta_ads_marketing_developer_token'  => ['nullable', 'string', 'max:500'],
            'meta_ads_sandbox_mode'               => [new OnOffRule()],
            'meta_ads_api_version'                => ['nullable', 'string', 'max:10'],
        ];
    }
}
