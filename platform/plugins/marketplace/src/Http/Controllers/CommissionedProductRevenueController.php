<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Tables\CommissionedProductRevenueTable;

class CommissionedProductRevenueController extends BaseController
{
    public function index(CommissionedProductRevenueTable $table)
    {
        $this->pageTitle(__('Commissioned Products Revenue'));

        return $table->renderTable();
    }
}
