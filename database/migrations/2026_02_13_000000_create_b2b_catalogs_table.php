<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('b2b_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('pdf_path');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('uploaded_by_type', 20)->default('admin'); // admin or vendor
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_catalogs');
    }
};
