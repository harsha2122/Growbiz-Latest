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
        'reach', 'frequency', 'cpm', 'ctr', 'cpc', 'conversions', 'conversion_value',
        'age_gender_breakdown', 'placement_breakdown', 'insights_synced_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_budget' => 'decimal:2',
        'lifetime_budget' => 'decimal:2',
        'spend' => 'decimal:2',
        'cpm' => 'decimal:2',
        'ctr' => 'decimal:2',
        'cpc' => 'decimal:2',
        'frequency' => 'decimal:2',
        'conversion_value' => 'decimal:2',
        'age_gender_breakdown' => 'array',
        'placement_breakdown' => 'array',
        'insights_synced_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function adSets()
    {
        return $this->hasMany(MetaAdSet::class, 'campaign_id');
    }

    public function dailyInsights()
    {
        return $this->hasMany(MetaAdInsightDaily::class, 'object_id')->where('object_type', 'campaign');
    }
}
