<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mp_service_clicks')) {
            Schema::create('mp_service_clicks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('service_id')->constrained('mp_services')->cascadeOnDelete();
                $table->string('visitor_key')->nullable();
                $table->timestamps();

                $table->index(['service_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_service_clicks');
    }
};
