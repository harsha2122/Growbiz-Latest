<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mp_stores') && ! Schema::hasColumn('mp_stores', 'establishment_date')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->date('establishment_date')->nullable()->after('store_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mp_stores') && Schema::hasColumn('mp_stores', 'establishment_date')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->dropColumn('establishment_date');
            });
        }
    }
};
