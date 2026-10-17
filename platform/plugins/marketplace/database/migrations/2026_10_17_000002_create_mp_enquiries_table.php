<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mp_enquiries')) {
            Schema::create('mp_enquiries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('ec_products')->nullOnDelete();
                $table->string('source', 20)->default('vendor_page'); // vendor_page | product_page
                $table->string('name');
                $table->string('contact_number', 30);
                $table->string('email');
                $table->text('address');
                $table->timestamps();

                $table->index(['store_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('mp_store_enquiry_visibility')) {
            Schema::create('mp_store_enquiry_visibility', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->unique()->constrained('mp_stores')->cascadeOnDelete();
                $table->boolean('details_visible')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_store_enquiry_visibility');
        Schema::dropIfExists('mp_enquiries');
    }
};
