<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all products in the 'services' category and update their product_type to 'service'
        $serviceProducts = DB::table('ec_product_categories')
            ->where('category_id', function ($query) {
                $query->select('id')
                    ->from('ec_product_categories')
                    ->where('slug', 'services');
            })
            ->pluck('product_id');

        if ($serviceProducts->count() > 0) {
            DB::table('ec_products')
                ->whereIn('id', $serviceProducts->toArray())
                ->where('product_type', '!=', 'service')
                ->update(['product_type' => 'service']);
        }
    }

    public function down(): void
    {
        //
    }
};
