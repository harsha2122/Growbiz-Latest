<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSponsoredVideo extends BaseModel
{
    protected $table = 'mp_store_sponsored_videos';

    protected $fillable = [
        'store_id',
        'video_url',
        'thumbnail',
        'expires_at',
        'sort_order',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function isActive(): bool
    {
        return ! ($this->expires_at && $this->expires_at->isPast());
    }
}
