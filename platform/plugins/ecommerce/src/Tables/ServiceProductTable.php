<?php

namespace Botble\Ecommerce\Tables;

use Illuminate\Database\Eloquent\Builder;

class ServiceProductTable extends ProductTable
{
    public function setup(): void
    {
        parent::setup();

        $this->modifyQueryUsing(function (Builder $query) {
            return $query->where('product_type', 'service');
        });
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('products.create', ['product_type' => 'service']));
    }
}
