<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('meta_ad_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('fb_user_id')->nullable();
            $table->string('fb_user_name')->nullable();
            $table->string('ad_account_id')->nullable();
            $table->string('ad_account_name')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_connected')->default(false);
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('mp_stores')->onDelete('cascade');
            $table->index('store_id');
        });

        Schema::create('meta_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('ad_account_id');
            $table->string('name');
            $table->string('objective')->default('OUTCOME_TRAFFIC');
            $table->string('status')->default('PAUSED');
            $table->decimal('daily_budget', 10, 2)->nullable();
            $table->decimal('lifetime_budget', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('meta_campaign_id')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('spend', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('mp_stores')->onDelete('cascade');
            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->onDelete('cascade');
            $table->index(['store_id', 'status']);
        });

        Schema::create('meta_ad_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('store_id');
            $table->string('name');
            $table->string('status')->default('PAUSED');
            $table->decimal('daily_budget', 10, 2)->nullable();
            $table->json('targeting_locations')->nullable();
            $table->unsignedTinyInteger('targeting_age_min')->default(18);
            $table->unsignedTinyInteger('targeting_age_max')->default(65);
            $table->string('targeting_genders')->default('all');
            $table->json('targeting_interests')->nullable();
            $table->json('placements')->nullable();
            $table->string('optimization_goal')->default('LINK_CLICKS');
            $table->string('meta_adset_id')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('spend', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('meta_campaigns')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('mp_stores')->onDelete('cascade');
            $table->index(['campaign_id', 'status']);
        });

        Schema::create('meta_ads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_set_id');
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('store_id');
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
            $table->string('meta_ad_id')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('spend', 10, 2)->default(0);
            $table->decimal('ctr', 5, 2)->default(0);
            $table->decimal('cpc', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('ad_set_id')->references('id')->on('meta_ad_sets')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('meta_campaigns')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('mp_stores')->onDelete('cascade');
            $table->index(['ad_set_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ads');
        Schema::dropIfExists('meta_ad_sets');
        Schema::dropIfExists('meta_campaigns');
        Schema::dropIfExists('meta_ad_accounts');
    }
};
