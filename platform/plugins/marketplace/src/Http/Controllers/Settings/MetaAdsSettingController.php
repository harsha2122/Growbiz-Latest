<?php

namespace Botble\Marketplace\Http\Controllers\Settings;

use Botble\Marketplace\Forms\Settings\MetaAdsSettingForm;
use Botble\Marketplace\Http\Requests\MetaAdsSettingRequest;

class MetaAdsSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(__('Meta Ads Settings'));

        return view('plugins/marketplace::settings.meta-ads', [
            'form' => MetaAdsSettingForm::create(),
        ]);
    }

    public function update(MetaAdsSettingRequest $request)
    {
        $data = $request->validated();

        // Never overwrite the app secret with a blank value
        if (empty($data['meta_app_secret'])) {
            unset($data['meta_app_secret']);
        }

        $this->saveSettings($data);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.meta-ads-settings'))
            ->withUpdatedSuccessMessage();
    }
}
