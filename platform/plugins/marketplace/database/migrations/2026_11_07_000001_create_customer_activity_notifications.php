<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_enquiries', fn (Blueprint $table) => $table->timestamp('seller_viewed_at')->nullable()->index());
        Schema::create('mp_activity_notifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('audience', 12);
            $table->string('kind', 32);
            $table->unsignedBigInteger('subject_id');
            $table->string('title');
            $table->string('message');
            $table->string('event_key', 100);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'audience', 'event_key'], 'mp_activity_recipient_event_unique');
            $table->index(['customer_id', 'audience', 'read_at'], 'mp_activity_recipient_unread_index');
            $table->index(['store_id', 'kind', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_activity_notifications');
        Schema::table('mp_enquiries', function (Blueprint $table): void {
            $table->dropIndex(['seller_viewed_at']);
            $table->dropColumn('seller_viewed_at');
        });
    }
};
