<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileReview extends BaseModel
{
    protected $table = 'mp_profile_reviews';

    protected $fillable = ['store_id', 'customer_id', 'star', 'comment', 'status'];

    public function getDisplayNameAttribute(): string
    {
        $name = $this->customer?->name ?: __('Customer');

        return get_ecommerce_setting('show_customer_full_name', true)
            ? $name
            : \Illuminate\Support\Str::mask($name, '*', 1);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
