<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mp_store_commission_settings')) {
            Schema::create('mp_store_commission_settings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->unique()->constrained('mp_stores')->cascadeOnDelete();
                $table->boolean('is_enabled')->default(false);
                $table->unsignedInteger('max_products')->nullable();
                $table->decimal('commission_percent', 5, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('ec_products', 'is_commissioned')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->boolean('is_commissioned')->default(false)->after('vendor_commission_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_store_commission_settings');

        if (Schema::hasColumn('ec_products', 'is_commissioned')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->dropColumn('is_commissioned');
            });
        }
    }
};
