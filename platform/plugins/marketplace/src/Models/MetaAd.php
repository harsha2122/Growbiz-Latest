<?php

namespace Botble\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAd extends Model
{
    protected $table = 'meta_ads';

    protected $fillable = [
        'ad_set_id', 'campaign_id', 'store_id', 'name', 'status', 'format',
        'primary_text', 'headline', 'description', 'cta_button',
        'destination_url', 'image_url', 'product_id', 'meta_ad_id', 'meta_creative_id',
        'impressions', 'clicks', 'spend', 'ctr', 'cpc',
        'reach', 'frequency', 'cpm', 'conversions', 'conversion_value',
        'quality_ranking', 'engagement_rate_ranking', 'conversion_rate_ranking',
        'insights_synced_at',
    ];

    protected $casts = [
        'spend' => 'decimal:2',
        'ctr' => 'decimal:2',
        'cpc' => 'decimal:2',
        'cpm' => 'decimal:2',
        'frequency' => 'decimal:2',
        'conversion_value' => 'decimal:2',
        'insights_synced_at' => 'datetime',
    ];

    public function adSet()
    {
        return $this->belongsTo(MetaAdSet::class);
    }

    public function campaign()
    {
        return $this->belongsTo(MetaCampaign::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(\Botble\Ecommerce\Models\Product::class);
    }

    public function dailyInsights()
    {
        return $this->hasMany(MetaAdInsightDaily::class, 'object_id')->where('object_type', 'ad');
    }

    /**
     * Human-readable label for Meta's relevance diagnostic ranking values
     * (e.g. ABOVE_AVERAGE, AVERAGE, BELOW_AVERAGE_35, UNKNOWN).
     */
    public static function rankingLabel(?string $ranking): string
    {
        if (! $ranking || $ranking === 'UNKNOWN') {
            return 'Not enough data';
        }

        return ucwords(str_replace(['_', 'below average'], [' ', 'Below average'], strtolower($ranking)));
    }
}
