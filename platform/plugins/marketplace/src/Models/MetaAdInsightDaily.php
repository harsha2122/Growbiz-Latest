<?php

namespace Botble\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAdInsightDaily extends Model
{
    protected $table = 'meta_ad_insights_daily';

    protected $fillable = [
        'store_id', 'object_type', 'object_id', 'date',
        'impressions', 'clicks', 'spend', 'reach', 'frequency',
        'cpm', 'ctr', 'cpc', 'conversions', 'conversion_value',
    ];

    protected $casts = [
        'date' => 'date',
        'spend' => 'decimal:2',
        'frequency' => 'decimal:2',
        'cpm' => 'decimal:2',
        'ctr' => 'decimal:2',
        'cpc' => 'decimal:2',
        'conversion_value' => 'decimal:2',
    ];

    public const TYPE_CAMPAIGN = 'campaign';
    public const TYPE_AD_SET = 'ad_set';
    public const TYPE_AD = 'ad';
}
