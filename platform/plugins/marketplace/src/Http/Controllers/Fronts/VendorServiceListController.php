<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Tables\VendorServiceTable;

class VendorServiceListController extends BaseController
{
    public function index(VendorServiceTable $table)
    {
        $this->pageTitle(__('Services'));

        return $table->renderTable();
    }
}
