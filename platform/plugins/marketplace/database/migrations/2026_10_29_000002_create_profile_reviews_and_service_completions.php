<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mp_profile_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('ec_customers')->cascadeOnDelete();
            $table->unsignedTinyInteger('star');
            $table->text('comment')->nullable();
            $table->string('status', 30)->default('published')->index();
            $table->timestamps();
            $table->unique(['store_id', 'customer_id']);
        });
        Schema::create('mp_service_completions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('ec_customers')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('verified_by');
            $table->date('completed_at');
            $table->string('reference', 191);
            $table->timestamps();
            $table->index(['store_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_service_completions');
        Schema::dropIfExists('mp_profile_reviews');
    }
};
