<?php

namespace Botble\Marketplace\Http\Controllers\Settings;

use Botble\Marketplace\Forms\Settings\MetaAdsSettingForm;
use Botble\Marketplace\Http\Requests\MetaAdsSettingRequest;
use Illuminate\Http\Request;

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

    public function testOembed(Request $request)
    {
        $request->validate([
            'url' => ['required', 'url', 'max:500'],
        ]);

        $result = test_instagram_oembed($request->input('url'));

        return response()->json($result);
    }
}
