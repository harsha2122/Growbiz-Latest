<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ads')) {
            Schema::table('ads', function (Blueprint $table): void {
                if (! Schema::hasColumn('ads', 'video_file')) {
                    $table->string('video_file')->nullable()->after('video_url');
                }
                if (! Schema::hasColumn('ads', 'video_type')) {
                    $table->string('video_type')->default('external')->after('video_file'); // 'external' or 'local'
                }
                if (! Schema::hasColumn('ads', 'video_size')) {
                    $table->bigInteger('video_size')->nullable()->after('video_type');
                }

                $table->index('video_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ads')) {
            Schema::table('ads', function (Blueprint $table): void {
                if (Schema::hasColumn('ads', 'video_file')) {
                    $table->dropColumn('video_file');
                }
                if (Schema::hasColumn('ads', 'video_type')) {
                    $table->dropColumn('video_type');
                }
                if (Schema::hasColumn('ads', 'video_size')) {
                    $table->dropColumn('video_size');
                }
                $table->dropIndexIfExists('ads_video_type_index');
            });
        }
    }
};
