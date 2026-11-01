<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends BaseModel
{
    protected $table = 'mp_enquiries';

    protected $fillable = [
        'customer_id', 'store_id', 'product_id', 'source', 'name', 'contact_number', 'email', 'address',
    ];

    protected static function booted(): void
    {
        static::created(fn (self $enquiry) => app(\Botble\Marketplace\Services\ReferralVerificationService::class)->trackEnquiry($enquiry));
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
