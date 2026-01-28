<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table): void {
            $table->string('ad_media_type')->default('image')->after('ads_type');
            $table->string('video_url')->nullable()->after('ad_media_type');
            $table->string('video_thumbnail')->nullable()->after('video_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table): void {
            $table->dropColumn('ad_media_type');
            $table->dropColumn('video_url');
            $table->dropColumn('video_thumbnail');
        });
    }
};
