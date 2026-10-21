<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mp_vendor_payments')) {
            Schema::create('mp_vendor_payments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('transaction_id');
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->string('status', 20)->default('pending'); // pending | approved | declined
                $table->text('remarks')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['store_id', 'created_at']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_vendor_payments');
    }
};
