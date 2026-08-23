<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mp_store_visits')) {
            Schema::create('mp_store_visits', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('visitor_key');
                $table->date('visit_date');
                $table->timestamps();

                // One row per visitor per store per day - repeat views within the same
                // day don't inflate the count.
                $table->unique(['store_id', 'visitor_key', 'visit_date'], 'mp_store_visits_unique');
                $table->index(['store_id', 'visit_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_store_visits');
    }
};
