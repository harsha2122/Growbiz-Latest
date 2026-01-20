<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class ServiceProductSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'is_enabled_support_service_products' => $onOffRule = new OnOffRule(),
            'allow_guest_checkout_for_service_products' => $onOffRule,
            'require_customer_phone_for_services' => $onOffRule,
            'auto_complete_service_orders_after_payment' => $onOffRule,
        ];
    }
}
