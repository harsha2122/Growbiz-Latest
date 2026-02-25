<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaAdSet extends BaseModel
{
    protected $table = 'meta_ad_sets';

    protected $fillable = [
        'campaign_id',
        'store_id',
        'name',
        'status',
        'daily_budget',
        'targeting_locations',
        'targeting_age_min',
        'targeting_age_max',
        'targeting_genders',
        'targeting_interests',
        'placements',
        'optimization_goal',
        'impressions',
        'clicks',
        'spend',
    ];

    protected $casts = [
        'targeting_locations' => 'array',
        'targeting_interests' => 'array',
        'placements' => 'array',
        'daily_budget' => 'decimal:2',
        'spend' => 'decimal:2',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MetaCampaign::class, 'campaign_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(MetaAd::class, 'ad_set_id');
    }
}
