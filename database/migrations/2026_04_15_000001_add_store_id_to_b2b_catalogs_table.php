<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('b2b_catalogs', function (Blueprint $table): void {
            $table->unsignedBigInteger('store_id')->nullable()->after('id');
            $table->foreign('store_id')->references('id')->on('mp_stores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('b2b_catalogs', function (Blueprint $table): void {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
