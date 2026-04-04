<?php

namespace Botble\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAdAccount extends Model
{
    protected $table = 'meta_ad_accounts';

    protected $fillable = [
        'store_id', 'fb_user_id', 'fb_user_name', 'ad_account_id',
        'ad_account_name', 'fb_page_id', 'fb_page_name', 'access_token',
        'token_expires_at', 'is_connected', 'connected_at',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function campaigns()
    {
        return $this->hasMany(MetaCampaign::class, 'ad_account_id');
    }
}
