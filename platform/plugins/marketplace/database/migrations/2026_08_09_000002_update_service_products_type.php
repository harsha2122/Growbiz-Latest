<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get the 'services' category ID
        $servicesCategory = DB::table('ec_product_categories')
            ->where('slug', 'services')
            ->first();

        if ($servicesCategory) {
            // Get all product IDs in the services category
            $serviceProductIds = DB::table('ec_product_categorizables')
                ->where('category_id', $servicesCategory->id)
                ->pluck('reference_id')
                ->toArray();

            // Update those products to have product_type = 'service'
            if (!empty($serviceProductIds)) {
                DB::table('ec_products')
                    ->whereIn('id', $serviceProductIds)
                    ->where('product_type', '!=', 'service')
                    ->update(['product_type' => 'service']);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
