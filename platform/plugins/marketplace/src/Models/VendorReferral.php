<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorReferral extends BaseModel
{
    protected $table = 'mp_vendor_referrals';

    protected $fillable = [
        'referrer_store_id',
        'referee_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function referrerStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'referrer_store_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referee_id')->withDefault();
    }
}
