<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCompletion extends BaseModel
{
    protected $table = 'mp_service_completions';

    protected $fillable = ['store_id', 'customer_id', 'product_id', 'verified_by', 'completed_at', 'reference'];

    protected $casts = ['completed_at' => 'date'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
