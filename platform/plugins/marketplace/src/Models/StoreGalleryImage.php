<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreGalleryImage extends BaseModel
{
    protected $table = 'mp_store_gallery';

    protected $fillable = ['store_id', 'image'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
