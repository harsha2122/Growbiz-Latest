<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreBrand extends BaseModel
{
    protected $table = 'mp_store_brands';

    protected $fillable = ['store_id', 'name', 'logo'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
