<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Ecommerce\Http\Requests\Settings\ServiceProductSettingRequest;
use Botble\Setting\Forms\SettingForm;

class ServiceProductSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.service_product.service_products_settings'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.service_product.service_products_settings_description'))
            ->setValidatorClass(ServiceProductSettingRequest::class)
            ->add('is_enabled_support_service_products', 'onOffCheckbox', [
                'label' => trans('plugins/ecommerce::setting.service_product.form.enable_support_service_product'),
                'value' => (bool) get_ecommerce_setting('is_enabled_support_service_products', 1),
                'helper' => trans('plugins/ecommerce::setting.service_product.form.enable_support_service_product_helper'),
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.service-products-settings',
                ],
            ])
            ->add('open_service_products_settings', 'html', [
                'html' => sprintf(
                    '<fieldset class="form-fieldset mt-3 service-products-settings" style="display: %s;" data-bb-value="1">',
                    get_ecommerce_setting('is_enabled_support_service_products', 1) ? 'block' : 'none'
                ),
            ])
            ->add(
                'allow_guest_checkout_for_service_products',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.service_product.form.allow_guest_checkout_for_service_products'))
                    ->value((bool) get_ecommerce_setting('allow_guest_checkout_for_service_products', 0))
                    ->helperText(trans('plugins/ecommerce::setting.service_product.form.allow_guest_checkout_for_service_products_helper'))
            )
            ->add(
                'require_customer_phone_for_services',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.service_product.form.require_customer_phone_for_services'))
                    ->value((bool) get_ecommerce_setting('require_customer_phone_for_services', 1))
                    ->helperText(trans('plugins/ecommerce::setting.service_product.form.require_customer_phone_for_services_helper'))
            )
            ->add(
                'auto_complete_service_orders_after_payment',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.service_product.form.auto_complete_service_orders_after_payment'))
                    ->value((bool) get_ecommerce_setting('auto_complete_service_orders_after_payment', 0))
                    ->helperText(trans('plugins/ecommerce::setting.service_product.form.auto_complete_service_orders_after_payment_helper'))
            )
            ->add('close_service_products_settings', 'html', ['html' => '</fieldset>']);
    }
}
