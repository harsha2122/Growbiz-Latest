<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreEnquiryVisibility extends BaseModel
{
    protected $table = 'mp_store_enquiry_visibility';

    protected $fillable = ['store_id', 'details_visible', 'expires_at'];

    protected $casts = ['details_visible' => 'boolean', 'expires_at' => 'datetime'];

    public function isActive(): bool
    {
        return $this->details_visible && $this->expires_at !== null && $this->expires_at->isFuture();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
