<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_activity_notifications', function (Blueprint $table): void {
            $table->timestamp('push_sent_at')->nullable();
            $table->unsignedTinyInteger('push_attempts')->default(0);
        });
        Schema::create('mp_activity_push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('audience', 12);
            $table->string('endpoint_hash', 64);
            $table->unique(['endpoint_hash', 'audience']);
            $table->text('subscription');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_activity_push_subscriptions');
        Schema::table('mp_activity_notifications', fn (Blueprint $table) => $table->dropColumn(['push_sent_at', 'push_attempts']));
    }
};
