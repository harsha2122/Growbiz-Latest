<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static VendorPaymentStatusEnum PENDING()
 * @method static VendorPaymentStatusEnum APPROVED()
 * @method static VendorPaymentStatusEnum DECLINED()
 */
class VendorPaymentStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const DECLINED = 'declined';

    public static $langPath = 'plugins/marketplace::marketplace.vendor_payment_statuses';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-yellow text-yellow-fg']),
            self::APPROVED => Html::tag('span', self::APPROVED()->label(), ['class' => 'badge bg-green text-green-fg']),
            self::DECLINED => Html::tag('span', self::DECLINED()->label(), ['class' => 'badge bg-red text-red-fg']),
            default => parent::toHtml(),
        };
    }
}
