<?php

namespace Botble\Payment\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static PaymentMethodEnum COD()
 * @method static PaymentMethodEnum BANK_TRANSFER()
 * @method static PaymentMethodEnum PAY_AFTER_SERVICE()
 */
class PaymentMethodEnum extends Enum
{
    public const COD = 'cod';
    public const BANK_TRANSFER = 'bank_transfer';
    public const PAY_AFTER_SERVICE = 'pay_after_service';

    public static $langPath = 'plugins/payment::payment.methods';

    public function getServiceClass(): ?string
    {
        return apply_filters(PAYMENT_FILTER_GET_SERVICE_CLASS, null, (string) $this->value);
    }

    public function displayName(): ?string
    {
        if ($label = get_payment_setting('name', $this->value)) {
            return $label;
        }

        return parent::label();
    }
}
