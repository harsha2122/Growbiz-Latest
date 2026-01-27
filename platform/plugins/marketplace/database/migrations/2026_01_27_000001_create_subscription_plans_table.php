<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('mp_subscription_plans')) {
            Schema::create('mp_subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('max_products')->default(10);
                $table->integer('duration_days')->default(30);
                $table->decimal('price', 15, 2)->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mp_vendor_subscriptions')) {
            Schema::create('mp_vendor_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('mp_subscription_plans')->cascadeOnDelete();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('status')->default('active');
                $table->foreignId('assigned_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_vendor_subscriptions');
        Schema::dropIfExists('mp_subscription_plans');
    }
};
