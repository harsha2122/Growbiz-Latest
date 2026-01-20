<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Ecommerce\Forms\Settings\ServiceProductSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\ServiceProductSettingRequest;

class ServiceProductSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.service_product.name'));

        return ServiceProductSettingForm::create()->renderForm();
    }

    public function update(ServiceProductSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
