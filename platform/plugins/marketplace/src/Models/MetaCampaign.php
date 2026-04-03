<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaCampaign extends BaseModel
{
    protected $table = 'meta_campaigns';

    protected $fillable = [
        'store_id',
        'ad_account_id',
        'name',
        'objective',
        'status',
        'daily_budget',
        'lifetime_budget',
        'start_date',
        'end_date',
        'meta_campaign_id',
        'impressions',
        'clicks',
        'spend',
    ];

    protected $casts = [
        'daily_budget' => 'decimal:2',
        'lifetime_budget' => 'decimal:2',
        'spend' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }

    public function adSets(): HasMany
    {
        return $this->hasMany(MetaAdSet::class, 'campaign_id');
    }

    public function ads(): HasMany
    {
        return $this->hasMany(MetaAd::class, 'campaign_id');
    }

    public function getCtrAttribute(): float
    {
        return $this->impressions > 0 ? round(($this->clicks / $this->impressions) * 100, 2) : 0;
    }

    public function getCpcAttribute(): float
    {
        return $this->clicks > 0 ? round($this->spend / $this->clicks, 2) : 0;
    }
}
