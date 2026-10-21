<?php

namespace Botble\Marketplace\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Marketplace\Enums\VendorPaymentStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayment extends BaseModel
{
    protected $table = 'mp_vendor_payments';

    protected $fillable = [
        'store_id',
        'transaction_id',
        'amount',
        'payment_date',
        'status',
        'remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
        'reviewed_at' => 'datetime',
        'status' => VendorPaymentStatusEnum::class,
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status == VendorPaymentStatusEnum::PENDING;
    }
}
