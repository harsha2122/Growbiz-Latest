<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mp_store_sponsored_videos')) {
            Schema::table('mp_store_sponsored_videos', function (Blueprint $table): void {
                // Add new columns for local video storage
                if (! Schema::hasColumn('mp_store_sponsored_videos', 'video_file')) {
                    $table->string('video_file')->nullable()->after('video_url');
                }
                if (! Schema::hasColumn('mp_store_sponsored_videos', 'video_type')) {
                    $table->string('video_type')->default('external')->after('video_file'); // 'external' or 'local'
                }
                if (! Schema::hasColumn('mp_store_sponsored_videos', 'video_size')) {
                    $table->bigInteger('video_size')->nullable()->after('video_type');
                }
                if (! Schema::hasColumn('mp_store_sponsored_videos', 'scheduled_deletion_at')) {
                    $table->timestamp('scheduled_deletion_at')->nullable()->after('expires_at');
                }

                // Add indexes for performance
                if (! Schema::hasIndexPath('mp_store_sponsored_videos', ['video_type'])) {
                    $table->index('video_type');
                }
                if (! Schema::hasIndexPath('mp_store_sponsored_videos', ['scheduled_deletion_at'])) {
                    $table->index('scheduled_deletion_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mp_store_sponsored_videos')) {
            Schema::table('mp_store_sponsored_videos', function (Blueprint $table): void {
                if (Schema::hasColumn('mp_store_sponsored_videos', 'video_file')) {
                    $table->dropColumn('video_file');
                }
                if (Schema::hasColumn('mp_store_sponsored_videos', 'video_type')) {
                    $table->dropColumn('video_type');
                }
                if (Schema::hasColumn('mp_store_sponsored_videos', 'video_size')) {
                    $table->dropColumn('video_size');
                }
                if (Schema::hasColumn('mp_store_sponsored_videos', 'scheduled_deletion_at')) {
                    $table->dropColumn('scheduled_deletion_at');
                }
                $table->dropIndexIfExists('mp_store_sponsored_videos_video_type_index');
                $table->dropIndexIfExists('mp_store_sponsored_videos_scheduled_deletion_at_index');
            });
        }
    }
};
