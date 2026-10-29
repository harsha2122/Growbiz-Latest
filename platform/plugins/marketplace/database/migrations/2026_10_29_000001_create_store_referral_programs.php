<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mp_referral_programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained('mp_stores')->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->string('mode', 20)->default('purchase');
            $table->string('reward_type', 20)->default('fixed');
            $table->decimal('reward_value', 14, 2)->default(0);
            $table->decimal('reward_cap', 14, 2)->nullable();
            $table->decimal('budget', 14, 2)->default(0);
            $table->decimal('reserved', 14, 2)->default(0);
            $table->string('offer_type', 20)->default('none');
            $table->decimal('offer_value', 14, 2)->default(0);
            $table->string('offer_text', 500)->nullable();
            $table->decimal('minimum_order', 14, 2)->default(0);
            $table->unsignedInteger('hold_days')->default(30);
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
        Schema::create('mp_referral_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_id')->constrained('mp_referral_programs')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('ec_customers')->cascadeOnDelete();
            $table->string('token', 48)->unique();
            $table->timestamps();
            $table->unique(['program_id', 'customer_id']);
        });
        Schema::create('mp_customer_referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_id')->constrained('mp_referral_programs')->cascadeOnDelete();
            $table->foreignId('link_id')->constrained('mp_referral_links')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('ec_customers')->cascadeOnDelete();
            $table->unsignedBigInteger('referrer_id')->index();
            $table->unsignedBigInteger('store_id')->index();
            $table->unsignedBigInteger('coupon_id')->nullable()->unique();
            $table->unsignedBigInteger('order_id')->nullable()->unique();
            $table->json('terms');
            $table->timestamp('expires_at');
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->string('status', 24)->default('tracked')->index();
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('reserved_amount', 14, 2)->default(0);
            $table->string('payout_reference', 191)->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('payout_requested_at')->nullable();
            $table->string('reason', 191)->nullable();
            $table->timestamps();
            $table->unique(['program_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_customer_referrals');
        Schema::dropIfExists('mp_referral_links');
        Schema::dropIfExists('mp_referral_programs');
    }
};
