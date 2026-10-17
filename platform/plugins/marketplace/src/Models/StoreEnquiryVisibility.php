<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreEnquiryVisibility extends BaseModel
{
    protected $table = 'mp_store_enquiry_visibility';

    protected $fillable = ['store_id', 'details_visible'];

    protected $casts = ['details_visible' => 'boolean'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
