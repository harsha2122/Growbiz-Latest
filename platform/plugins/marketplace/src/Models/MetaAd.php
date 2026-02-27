<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAd extends BaseModel
{
    protected $table = 'meta_ads';

    protected $fillable = [
        'ad_set_id',
        'store_id',
        'campaign_id',
        'meta_remote_id',
        'name',
        'status',
        'format',
        'primary_text',
        'headline',
        'description',
        'cta_button',
        'destination_url',
        'image_url',
        'product_id',
        'impressions',
        'clicks',
        'spend',
        'ctr',
        'cpc',
    ];

    protected $casts = [
        'spend' => 'decimal:2',
        'ctr' => 'decimal:2',
        'cpc' => 'decimal:2',
    ];

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(MetaAdSet::class, 'ad_set_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MetaCampaign::class, 'campaign_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
