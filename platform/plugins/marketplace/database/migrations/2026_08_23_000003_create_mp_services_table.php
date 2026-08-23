<?php

use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mp_services')) {
            Schema::create('mp_services', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('store_id')->constrained('mp_stores')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->decimal('price', 15, 2)->nullable();
                $table->string('status', 60)->default(BaseStatusEnum::PUBLISHED);
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_services');
    }
};
