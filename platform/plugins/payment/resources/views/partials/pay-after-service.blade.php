<x-plugins-payment::payment-method
    :name="\Botble\Payment\Enums\PaymentMethodEnum::PAY_AFTER_SERVICE"
    :label="get_payment_setting('name', 'pay_after_service', trans('plugins/payment::payment.payment_via_pay_after_service'))"
/>
