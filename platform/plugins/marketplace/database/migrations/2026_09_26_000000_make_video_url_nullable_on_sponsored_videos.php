<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mp_store_sponsored_videos')) {
            Schema::table('mp_store_sponsored_videos', function (Blueprint $table): void {
                $table->string('video_url')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mp_store_sponsored_videos')) {
            Schema::table('mp_store_sponsored_videos', function (Blueprint $table): void {
                $table->string('video_url')->nullable(false)->change();
            });
        }
    }
};
