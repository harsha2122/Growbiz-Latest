<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('meta_campaigns') && ! Schema::hasColumn('meta_campaigns', 'meta_remote_id')) {
            Schema::table('meta_campaigns', function (Blueprint $table) {
                $table->string('meta_remote_id')->nullable()->after('ad_account_id')->index();
            });
        }

        if (Schema::hasTable('meta_ad_sets') && ! Schema::hasColumn('meta_ad_sets', 'meta_remote_id')) {
            Schema::table('meta_ad_sets', function (Blueprint $table) {
                $table->string('meta_remote_id')->nullable()->after('campaign_id')->index();
            });
        }

        if (Schema::hasTable('meta_ads') && ! Schema::hasColumn('meta_ads', 'meta_remote_id')) {
            Schema::table('meta_ads', function (Blueprint $table) {
                $table->string('meta_remote_id')->nullable()->after('campaign_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('meta_campaigns', 'meta_remote_id')) {
            Schema::table('meta_campaigns', function (Blueprint $table) {
                $table->dropColumn('meta_remote_id');
            });
        }

        if (Schema::hasColumn('meta_ad_sets', 'meta_remote_id')) {
            Schema::table('meta_ad_sets', function (Blueprint $table) {
                $table->dropColumn('meta_remote_id');
            });
        }

        if (Schema::hasColumn('meta_ads', 'meta_remote_id')) {
            Schema::table('meta_ads', function (Blueprint $table) {
                $table->dropColumn('meta_remote_id');
            });
        }
    }
};
