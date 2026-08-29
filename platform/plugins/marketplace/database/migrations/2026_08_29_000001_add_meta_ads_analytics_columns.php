<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('meta_campaigns')) {
            Schema::table('meta_campaigns', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_campaigns', 'reach')) {
                    $table->unsignedBigInteger('reach')->default(0)->after('clicks');
                }
                if (! Schema::hasColumn('meta_campaigns', 'frequency')) {
                    $table->decimal('frequency', 8, 2)->default(0)->after('reach');
                }
                if (! Schema::hasColumn('meta_campaigns', 'cpm')) {
                    $table->decimal('cpm', 10, 2)->default(0)->after('spend');
                }
                if (! Schema::hasColumn('meta_campaigns', 'ctr')) {
                    $table->decimal('ctr', 6, 2)->default(0)->after('cpm');
                }
                if (! Schema::hasColumn('meta_campaigns', 'cpc')) {
                    $table->decimal('cpc', 10, 2)->default(0)->after('ctr');
                }
                if (! Schema::hasColumn('meta_campaigns', 'conversions')) {
                    $table->unsignedInteger('conversions')->default(0)->after('cpc');
                }
                if (! Schema::hasColumn('meta_campaigns', 'conversion_value')) {
                    $table->decimal('conversion_value', 12, 2)->default(0)->after('conversions');
                }
                if (! Schema::hasColumn('meta_campaigns', 'age_gender_breakdown')) {
                    $table->json('age_gender_breakdown')->nullable()->after('conversion_value');
                }
                if (! Schema::hasColumn('meta_campaigns', 'placement_breakdown')) {
                    $table->json('placement_breakdown')->nullable()->after('age_gender_breakdown');
                }
                if (! Schema::hasColumn('meta_campaigns', 'insights_synced_at')) {
                    $table->timestamp('insights_synced_at')->nullable()->after('placement_breakdown');
                }
            });
        }

        if (Schema::hasTable('meta_ad_sets')) {
            Schema::table('meta_ad_sets', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_ad_sets', 'reach')) {
                    $table->unsignedBigInteger('reach')->default(0)->after('clicks');
                }
                if (! Schema::hasColumn('meta_ad_sets', 'frequency')) {
                    $table->decimal('frequency', 8, 2)->default(0)->after('reach');
                }
                if (! Schema::hasColumn('meta_ad_sets', 'cpm')) {
                    $table->decimal('cpm', 10, 2)->default(0)->after('spend');
                }
                if (! Schema::hasColumn('meta_ad_sets', 'ctr')) {
                    $table->decimal('ctr', 6, 2)->default(0)->after('cpm');
                }
                if (! Schema::hasColumn('meta_ad_sets', 'cpc')) {
                    $table->decimal('cpc', 10, 2)->default(0)->after('ctr');
                }
                if (! Schema::hasColumn('meta_ad_sets', 'conversions')) {
                    $table->unsignedInteger('conversions')->default(0)->after('cpc');
                }
                if (! Schema::hasColumn('meta_ad_sets', 'conversion_value')) {
                    $table->decimal('conversion_value', 12, 2)->default(0)->after('conversions');
                }
                if (! Schema::hasColumn('meta_ad_sets', 'insights_synced_at')) {
                    $table->timestamp('insights_synced_at')->nullable()->after('conversion_value');
                }
            });
        }

        if (Schema::hasTable('meta_ads')) {
            Schema::table('meta_ads', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_ads', 'reach')) {
                    $table->unsignedBigInteger('reach')->default(0)->after('clicks');
                }
                if (! Schema::hasColumn('meta_ads', 'frequency')) {
                    $table->decimal('frequency', 8, 2)->default(0)->after('reach');
                }
                if (! Schema::hasColumn('meta_ads', 'cpm')) {
                    $table->decimal('cpm', 10, 2)->default(0)->after('spend');
                }
                if (! Schema::hasColumn('meta_ads', 'conversions')) {
                    $table->unsignedInteger('conversions')->default(0)->after('cpc');
                }
                if (! Schema::hasColumn('meta_ads', 'conversion_value')) {
                    $table->decimal('conversion_value', 12, 2)->default(0)->after('conversions');
                }
                if (! Schema::hasColumn('meta_ads', 'quality_ranking')) {
                    $table->string('quality_ranking')->nullable()->after('conversion_value');
                }
                if (! Schema::hasColumn('meta_ads', 'engagement_rate_ranking')) {
                    $table->string('engagement_rate_ranking')->nullable()->after('quality_ranking');
                }
                if (! Schema::hasColumn('meta_ads', 'conversion_rate_ranking')) {
                    $table->string('conversion_rate_ranking')->nullable()->after('engagement_rate_ranking');
                }
                if (! Schema::hasColumn('meta_ads', 'insights_synced_at')) {
                    $table->timestamp('insights_synced_at')->nullable()->after('conversion_rate_ranking');
                }
            });
        }

        if (Schema::hasTable('meta_ad_accounts')) {
            Schema::table('meta_ad_accounts', function (Blueprint $table) {
                if (! Schema::hasColumn('meta_ad_accounts', 'currency')) {
                    $table->string('currency', 10)->nullable();
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'account_status')) {
                    $table->unsignedTinyInteger('account_status')->nullable();
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'amount_spent')) {
                    $table->decimal('amount_spent', 14, 2)->default(0);
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'spend_cap')) {
                    $table->decimal('spend_cap', 14, 2)->nullable();
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'balance')) {
                    $table->decimal('balance', 14, 2)->nullable();
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'timezone_name')) {
                    $table->string('timezone_name')->nullable();
                }
                if (! Schema::hasColumn('meta_ad_accounts', 'has_payment_method')) {
                    $table->boolean('has_payment_method')->nullable();
                }
            });
        }

        if (! Schema::hasTable('meta_ad_insights_daily')) {
            Schema::create('meta_ad_insights_daily', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->index();
                $table->string('object_type', 20); // campaign|ad_set|ad
                $table->unsignedBigInteger('object_id');
                $table->date('date');
                $table->unsignedBigInteger('impressions')->default(0);
                $table->unsignedBigInteger('clicks')->default(0);
                $table->decimal('spend', 12, 2)->default(0);
                $table->unsignedBigInteger('reach')->default(0);
                $table->decimal('frequency', 8, 2)->default(0);
                $table->decimal('cpm', 10, 2)->default(0);
                $table->decimal('ctr', 6, 2)->default(0);
                $table->decimal('cpc', 10, 2)->default(0);
                $table->unsignedInteger('conversions')->default(0);
                $table->decimal('conversion_value', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(['object_type', 'object_id', 'date'], 'meta_insights_daily_object_date_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_insights_daily');

        foreach (['currency', 'account_status', 'amount_spent', 'spend_cap', 'balance', 'timezone_name', 'has_payment_method'] as $column) {
            if (Schema::hasColumn('meta_ad_accounts', $column)) {
                Schema::table('meta_ad_accounts', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }

        foreach (['reach', 'frequency', 'cpm', 'ctr', 'cpc', 'conversions', 'conversion_value', 'age_gender_breakdown', 'placement_breakdown', 'insights_synced_at'] as $column) {
            if (Schema::hasColumn('meta_campaigns', $column)) {
                Schema::table('meta_campaigns', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }

        foreach (['reach', 'frequency', 'cpm', 'ctr', 'cpc', 'conversions', 'conversion_value', 'insights_synced_at'] as $column) {
            if (Schema::hasColumn('meta_ad_sets', $column)) {
                Schema::table('meta_ad_sets', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }

        foreach (['reach', 'frequency', 'cpm', 'conversions', 'conversion_value', 'quality_ranking', 'engagement_rate_ranking', 'conversion_rate_ranking', 'insights_synced_at'] as $column) {
            if (Schema::hasColumn('meta_ads', $column)) {
                Schema::table('meta_ads', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
    }
};
