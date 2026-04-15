<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('b2b_catalog_pdfs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('b2b_catalog_id');
            $table->string('title');
            $table->string('pdf_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('b2b_catalog_id')->references('id')->on('b2b_catalogs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_catalog_pdfs');
    }
};
