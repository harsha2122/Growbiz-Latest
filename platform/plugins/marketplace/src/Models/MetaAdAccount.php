<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaAdAccount extends BaseModel
{
    protected $table = 'meta_ad_accounts';

    protected $fillable = [
        'store_id',
        'fb_user_id',
        'fb_user_name',
        'ad_account_id',
        'ad_account_name',
        'access_token',
        'token_expires_at',
        'is_connected',
        'connected_at',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(MetaCampaign::class, 'ad_account_id');
    }
}
