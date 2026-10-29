<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReferral extends BaseModel
{
    protected $table = 'mp_customer_referrals';

    protected $guarded = ['id'];

    protected $casts = ['terms' => 'array', 'expires_at' => 'datetime', 'qualified_at' => 'datetime', 'available_at' => 'datetime', 'paid_at' => 'datetime', 'payout_requested_at' => 'datetime'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ReferralProgram::class, 'program_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'coupon_id');
    }
}
