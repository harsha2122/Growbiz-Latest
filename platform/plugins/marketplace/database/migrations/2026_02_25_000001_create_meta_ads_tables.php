<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('meta_ad_accounts')) {
            Schema::create('meta_ad_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('fb_user_id')->nullable();
                $table->string('fb_user_name')->nullable();
                $table->string('ad_account_id')->nullable();
                $table->string('ad_account_name')->nullable();
                $table->text('access_token')->nullable();
                $table->timestamp('token_expires_at')->nullable();
                $table->boolean('is_connected')->default(false);
                $table->timestamp('connected_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('meta_campaigns')) {
            Schema::create('meta_campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->foreignId('ad_account_id')->constrained('meta_ad_accounts')->cascadeOnDelete();
                $table->string('name');
                $table->string('objective')->default('TRAFFIC');
                $table->string('status')->default('PAUSED');
                $table->decimal('daily_budget', 10, 2)->nullable();
                $table->decimal('lifetime_budget', 10, 2)->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->integer('impressions')->default(0);
                $table->integer('clicks')->default(0);
                $table->decimal('spend', 10, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('meta_ad_sets')) {
            Schema::create('meta_ad_sets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('meta_campaigns')->cascadeOnDelete();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('name');
                $table->string('status')->default('PAUSED');
                $table->decimal('daily_budget', 10, 2)->nullable();
                $table->json('targeting_locations')->nullable();
                $table->integer('targeting_age_min')->default(18);
                $table->integer('targeting_age_max')->default(65);
                $table->string('targeting_genders')->default('all');
                $table->json('targeting_interests')->nullable();
                $table->json('placements')->nullable();
                $table->string('optimization_goal')->default('LINK_CLICKS');
                $table->integer('impressions')->default(0);
                $table->integer('clicks')->default(0);
                $table->decimal('spend', 10, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('meta_ads')) {
            Schema::create('meta_ads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ad_set_id')->constrained('meta_ad_sets')->cascadeOnDelete();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->foreignId('campaign_id')->constrained('meta_campaigns')->cascadeOnDelete();
                $table->string('name');
                $table->string('status')->default('IN_REVIEW');
                $table->string('format')->default('SINGLE_IMAGE');
                $table->text('primary_text')->nullable();
                $table->string('headline')->nullable();
                $table->text('description')->nullable();
                $table->string('cta_button')->default('LEARN_MORE');
                $table->string('destination_url')->nullable();
                $table->string('image_url')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('impressions')->default(0);
                $table->integer('clicks')->default(0);
                $table->decimal('spend', 10, 2)->default(0);
                $table->decimal('ctr', 5, 2)->default(0);
                $table->decimal('cpc', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ads');
        Schema::dropIfExists('meta_ad_sets');
        Schema::dropIfExists('meta_campaigns');
        Schema::dropIfExists('meta_ad_accounts');
    }
};
