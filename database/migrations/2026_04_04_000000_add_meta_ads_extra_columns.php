<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('meta_ad_accounts')) {
            Schema::table('meta_ad_accounts', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_ad_accounts', 'fb_page_id')) {
                    $table->string('fb_page_id')->nullable()->after('ad_account_name');
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'fb_page_name')) {
                    $table->string('fb_page_name')->nullable()->after('fb_page_id');
                }
            });
        }

        if (Schema::hasTable('meta_ads')) {
            Schema::table('meta_ads', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_ads', 'meta_creative_id')) {
                    $table->string('meta_creative_id')->nullable()->after('meta_ad_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('meta_ad_accounts')) {
            Schema::table('meta_ad_accounts', function (Blueprint $table) {
                $table->dropColumn(['fb_page_id', 'fb_page_name']);
            });
        }

        if (Schema::hasTable('meta_ads')) {
            Schema::table('meta_ads', function (Blueprint $table) {
                $table->dropColumn('meta_creative_id');
            });
        }
    }
};
