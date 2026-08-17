<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('b2b_catalogs') && Schema::hasColumn('b2b_catalogs', 'pdf_path')) {
            Schema::table('b2b_catalogs', function (Blueprint $table) {
                $table->string('pdf_path')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('b2b_catalogs') && Schema::hasColumn('b2b_catalogs', 'pdf_path')) {
            Schema::table('b2b_catalogs', function (Blueprint $table) {
                $table->string('pdf_path')->nullable(false)->change();
            });
        }
    }
};
