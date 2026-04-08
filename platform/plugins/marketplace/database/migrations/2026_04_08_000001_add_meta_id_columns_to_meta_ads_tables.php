<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // meta_campaigns — add meta_campaign_id
        if (Schema::hasTable('meta_campaigns') && ! Schema::hasColumn('meta_campaigns', 'meta_campaign_id')) {
            Schema::table('meta_campaigns', function (Blueprint $table) {
                $table->string('meta_campaign_id')->nullable()->index();
            });
        }

        // meta_ad_sets — add meta_adset_id
        if (Schema::hasTable('meta_ad_sets') && ! Schema::hasColumn('meta_ad_sets', 'meta_adset_id')) {
            Schema::table('meta_ad_sets', function (Blueprint $table) {
                $table->string('meta_adset_id')->nullable()->index();
            });
        }

        // meta_ads — add meta_ad_id and meta_creative_id
        if (Schema::hasTable('meta_ads')) {
            Schema::table('meta_ads', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_ads', 'meta_ad_id')) {
                    $table->string('meta_ad_id')->nullable()->index();
                }
                if (! Schema::hasColumn('meta_ads', 'meta_creative_id')) {
                    $table->string('meta_creative_id')->nullable();
                }
            });
        }

        // meta_ad_accounts — add fb_page_id and fb_page_name
        if (Schema::hasTable('meta_ad_accounts')) {
            Schema::table('meta_ad_accounts', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_ad_accounts', 'fb_page_id')) {
                    $table->string('fb_page_id')->nullable();
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'fb_page_name')) {
                    $table->string('fb_page_name')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('meta_campaigns', 'meta_campaign_id')) {
            Schema::table('meta_campaigns', fn (Blueprint $t) => $t->dropColumn('meta_campaign_id'));
        }
        if (Schema::hasColumn('meta_ad_sets', 'meta_adset_id')) {
            Schema::table('meta_ad_sets', fn (Blueprint $t) => $t->dropColumn('meta_adset_id'));
        }
        if (Schema::hasColumn('meta_ads', 'meta_ad_id')) {
            Schema::table('meta_ads', fn (Blueprint $t) => $t->dropColumn('meta_ad_id'));
        }
        if (Schema::hasColumn('meta_ads', 'meta_creative_id')) {
            Schema::table('meta_ads', fn (Blueprint $t) => $t->dropColumn('meta_creative_id'));
        }
    }
};
