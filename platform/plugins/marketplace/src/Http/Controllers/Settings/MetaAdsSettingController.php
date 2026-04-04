<?php

namespace Botble\Marketplace\Http\Controllers\Settings;

use Botble\Marketplace\Forms\Settings\MetaAdsSettingForm;
use Botble\Marketplace\Http\Requests\MetaAdsSettingRequest;

class MetaAdsSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle('Meta Ads Integration');

        return view('plugins/marketplace::settings.meta-ads', [
            'form' => MetaAdsSettingForm::create(),
        ]);
    }

    public function update(MetaAdsSettingRequest $request)
    {
        $this->saveSettings($request->validated());

        return $this->httpResponse()
            ->setNextUrl(route('marketplace.meta-ads-settings'))
            ->withUpdatedSuccessMessage();
    }
}
