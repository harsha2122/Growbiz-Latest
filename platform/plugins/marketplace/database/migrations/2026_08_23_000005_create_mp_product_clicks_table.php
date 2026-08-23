<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mp_product_clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('ec_products')->cascadeOnDelete();
            $table->string('visitor_key')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_product_clicks');
    }
};
