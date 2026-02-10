<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->boolean('is_key_account')->default(false)->after('is_verified');
        });

        Schema::table('ec_products', function (Blueprint $table) {
            $table->decimal('vendor_commission', 8, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->dropColumn('is_key_account');
        });

        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropColumn('vendor_commission');
        });
    }
};
