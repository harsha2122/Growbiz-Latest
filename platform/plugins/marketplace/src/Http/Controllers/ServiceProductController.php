<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Tables\ServiceProductTable;

class ServiceProductController extends BaseController
{
    public function index(ServiceProductTable $table)
    {
        $this->pageTitle(__('Services'));

        return $table->renderTable();
    }
}
