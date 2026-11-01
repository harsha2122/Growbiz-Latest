<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralEvent extends BaseModel
{
    protected $table = 'mp_referral_events';

    protected $guarded = ['id'];

    protected $casts = ['occurred_on' => 'date', 'customer_confirmed_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public function referral(): BelongsTo
    {
        return $this->belongsTo(CustomerReferral::class, 'referral_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function eligibleAmount(): float
    {
        return round(max(0, (float) $this->subtotal - (float) $this->discount - (float) $this->refunded_amount), 2);
    }
}
