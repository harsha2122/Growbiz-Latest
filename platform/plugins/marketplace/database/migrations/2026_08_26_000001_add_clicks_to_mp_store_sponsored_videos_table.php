<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mp_store_sponsored_videos') && ! Schema::hasColumn('mp_store_sponsored_videos', 'clicks')) {
            Schema::table('mp_store_sponsored_videos', function (Blueprint $table): void {
                $table->unsignedBigInteger('clicks')->default(0)->after('sort_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mp_store_sponsored_videos') && Schema::hasColumn('mp_store_sponsored_videos', 'clicks')) {
            Schema::table('mp_store_sponsored_videos', function (Blueprint $table): void {
                $table->dropColumn('clicks');
            });
        }
    }
};
