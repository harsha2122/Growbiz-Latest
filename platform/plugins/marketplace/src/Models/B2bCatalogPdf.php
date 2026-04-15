<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2bCatalogPdf extends BaseModel
{
    protected $table = 'b2b_catalog_pdfs';

    protected $fillable = [
        'b2b_catalog_id',
        'title',
        'pdf_path',
        'sort_order',
    ];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(B2bCatalog::class, 'b2b_catalog_id');
    }
}
