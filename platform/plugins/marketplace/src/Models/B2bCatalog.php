<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class B2bCatalog extends BaseModel
{
    protected $table = 'b2b_catalogs';

    protected $fillable = [
        'store_id',
        'title',
        'description',
        'discount_percentage',
        'pdf_path',
        'contact_number',
        'whatsapp_number',
        'uploaded_by',
        'uploaded_by_type',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id')->withDefault();
    }

    public function pdfs(): HasMany
    {
        return $this->hasMany(B2bCatalogPdf::class, 'b2b_catalog_id')->orderBy('sort_order');
    }
}
