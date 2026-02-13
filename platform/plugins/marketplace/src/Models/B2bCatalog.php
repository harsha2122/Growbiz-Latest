<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;

class B2bCatalog extends BaseModel
{
    protected $table = 'b2b_catalogs';

    protected $fillable = [
        'title',
        'description',
        'pdf_path',
        'uploaded_by',
        'uploaded_by_type',
    ];
}
