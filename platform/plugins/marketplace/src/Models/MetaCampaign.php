<?php

namespace Botble\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;

class MetaCampaign extends Model
{
    protected $table = 'meta_campaigns';

    protected $fillable = [
        'store_id', 'ad_account_id', 'name', 'objective', 'status',
        'daily_budget', 'lifetime_budget', 'start_date', 'end_date',
        'meta_campaign_id', 'impressions', 'clicks', 'spend',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_budget' => 'decimal:2',
        'lifetime_budget' => 'decimal:2',
        'spend' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function adSets()
    {
        return $this->hasMany(MetaAdSet::class, 'campaign_id');
    }
}
