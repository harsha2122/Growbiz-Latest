<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('b2b_catalog_pdfs') && ! Schema::hasColumn('b2b_catalog_pdfs', 'thumbnail_path')) {
            Schema::table('b2b_catalog_pdfs', function (Blueprint $table) {
                $table->string('thumbnail_path')->nullable()->after('pdf_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('b2b_catalog_pdfs') && Schema::hasColumn('b2b_catalog_pdfs', 'thumbnail_path')) {
            Schema::table('b2b_catalog_pdfs', function (Blueprint $table) {
                $table->dropColumn('thumbnail_path');
            });
        }
    }
};
