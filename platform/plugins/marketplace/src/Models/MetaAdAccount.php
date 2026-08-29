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
        'currency', 'account_status', 'amount_spent', 'spend_cap', 'balance',
        'timezone_name', 'has_payment_method',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
        'amount_spent' => 'decimal:2',
        'spend_cap' => 'decimal:2',
        'balance' => 'decimal:2',
        'has_payment_method' => 'boolean',
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
