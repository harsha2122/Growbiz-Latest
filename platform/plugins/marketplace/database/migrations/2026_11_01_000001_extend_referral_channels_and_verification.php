<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_referral_programs', function (Blueprint $table): void {
            $table->string('channel', 12)->default('online');
            $table->string('enquiry_trigger', 24)->default('approved');
            $table->string('offer_image')->nullable();
            $table->text('extra_terms')->nullable();
        });
        Schema::table('mp_referral_links', fn (Blueprint $table) => $table->string('short_code', 40)->nullable()->unique());
        Schema::table('mp_customer_referrals', function (Blueprint $table): void {
            $table->string('pass_code', 40)->nullable()->unique();
            $table->unsignedBigInteger('event_id')->nullable()->unique();
        });
        Schema::table('mp_enquiries', fn (Blueprint $table) => $table->unsignedBigInteger('customer_id')->nullable()->index());
        Schema::create('mp_referral_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('referral_id')->constrained('mp_customer_referrals')->cascadeOnDelete();
            $table->unsignedBigInteger('store_id')->index();
            $table->string('kind', 24);
            $table->unsignedBigInteger('enquiry_id')->nullable()->unique();
            $table->string('reference', 191);
            $table->string('invoice_key', 64)->nullable();
            $table->string('receipt_hash', 64)->nullable();
            $table->string('evidence_path')->nullable();
            $table->date('occurred_on');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('shipping', 14, 2)->default(0);
            $table->decimal('refunded_amount', 14, 2)->default(0);
            $table->string('status', 24)->index();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('customer_confirmed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('reason', 191)->nullable();
            $table->timestamps();
            $table->unique(['store_id', 'invoice_key']);
            $table->unique(['store_id', 'receipt_hash']);
        });
        Schema::create('mp_referral_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('referral_id')->index();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('actor_role', 16);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 40);
            $table->json('details')->nullable();
            $table->timestamps();
        });
        DB::table('mp_referral_links')->orderBy('id')->each(function ($row): void {
            DB::table('mp_referral_links')->where('id', $row->id)->update(['short_code' => 'G-'.$row->id.'-'.Str::upper(Str::random(6))]);
        });
        DB::table('mp_customer_referrals')->orderBy('id')->each(function ($row): void {
            DB::table('mp_customer_referrals')->where('id', $row->id)->update(['pass_code' => 'RF-'.$row->id.'-'.Str::upper(Str::random(8))]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_referral_audits');
        Schema::dropIfExists('mp_referral_events');
        Schema::table('mp_enquiries', function (Blueprint $table): void {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::table('mp_customer_referrals', function (Blueprint $table): void {
            $table->dropUnique(['pass_code']);
            $table->dropUnique(['event_id']);
            $table->dropColumn(['pass_code', 'event_id']);
        });
        Schema::table('mp_referral_links', function (Blueprint $table): void {
            $table->dropUnique(['short_code']);
            $table->dropColumn('short_code');
        });
        Schema::table('mp_referral_programs', fn (Blueprint $table) => $table->dropColumn(['channel', 'enquiry_trigger', 'offer_image', 'extra_terms']));
    }
};
