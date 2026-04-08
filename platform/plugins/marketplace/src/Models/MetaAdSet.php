<?php

namespace Botble\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAdSet extends Model
{
    protected $table = 'meta_ad_sets';

    protected $fillable = [
        'campaign_id', 'store_id', 'name', 'status', 'daily_budget', 'bid_cap',
        'targeting_locations', 'targeting_age_min', 'targeting_age_max',
        'targeting_genders', 'targeting_interests', 'placements',
        'optimization_goal', 'meta_adset_id', 'impressions', 'clicks', 'spend',
    ];

    protected $casts = [
        'targeting_locations' => 'array',
        'targeting_interests' => 'array',
        'placements' => 'array',
        'daily_budget' => 'decimal:2',
        'bid_cap' => 'decimal:2',
        'spend' => 'decimal:2',
    ];

    public function campaign()
    {
        return $this->belongsTo(MetaCampaign::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function ads()
    {
        return $this->hasMany(MetaAd::class, 'ad_set_id');
    }
}
