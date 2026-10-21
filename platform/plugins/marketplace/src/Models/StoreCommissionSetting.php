<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCommissionSetting extends BaseModel
{
    protected $table = 'mp_store_commission_settings';

    protected $fillable = ['store_id', 'is_enabled', 'max_products', 'commission_percent'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'max_products' => 'integer',
        'commission_percent' => 'float',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
