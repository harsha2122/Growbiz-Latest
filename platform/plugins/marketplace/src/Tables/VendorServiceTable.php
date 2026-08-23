<?php

namespace Botble\Marketplace\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class VendorServiceTable extends ProductTable
{
    public function query(): Relation|Builder|QueryBuilder
    {
        return $this->applyScopes($this->baseQuery()->where('product_type', 'service'));
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('marketplace.vendor.products.create', ['product_type' => 'service']));
    }
}
