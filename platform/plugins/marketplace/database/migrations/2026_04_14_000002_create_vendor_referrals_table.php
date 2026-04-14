<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mp_vendor_referrals')) {
            Schema::create('mp_vendor_referrals', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('referrer_store_id');
                $table->unsignedBigInteger('referee_id');
                $table->timestamp('joined_at')->useCurrent();
                $table->timestamps();

                $table->foreign('referrer_store_id')->references('id')->on('mp_stores')->cascadeOnDelete();
                $table->foreign('referee_id')->references('id')->on('ec_customers')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_vendor_referrals');
    }
};
