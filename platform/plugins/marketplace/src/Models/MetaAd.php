<?php

namespace Botble\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAd extends Model
{
    protected $table = 'meta_ads';

    protected $fillable = [
        'ad_set_id', 'campaign_id', 'store_id', 'name', 'status', 'format',
        'primary_text', 'headline', 'description', 'cta_button',
        'destination_url', 'image_url', 'product_id', 'meta_ad_id',
        'impressions', 'clicks', 'spend', 'ctr', 'cpc',
    ];

    protected $casts = [
        'spend' => 'decimal:2',
        'ctr' => 'decimal:2',
        'cpc' => 'decimal:2',
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
}
