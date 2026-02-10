<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('mp_stores', 'is_key_account')) {
            Schema::table('mp_stores', function (Blueprint $table) {
                $table->boolean('is_key_account')->default(false)->after('is_verified');
            });
        }

        if (! Schema::hasColumn('ec_products', 'vendor_commission')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->decimal('vendor_commission', 8, 2)->nullable()->after('store_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mp_stores', 'is_key_account')) {
            Schema::table('mp_stores', function (Blueprint $table) {
                $table->dropColumn('is_key_account');
            });
        }

        if (Schema::hasColumn('ec_products', 'vendor_commission')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->dropColumn('vendor_commission');
            });
        }
    }
};
