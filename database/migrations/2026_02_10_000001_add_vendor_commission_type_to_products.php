<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_products', 'vendor_commission_type')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->string('vendor_commission_type', 20)->default('percentage')->after('vendor_commission');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_products', 'vendor_commission_type')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->dropColumn('vendor_commission_type');
            });
        }
    }
};
