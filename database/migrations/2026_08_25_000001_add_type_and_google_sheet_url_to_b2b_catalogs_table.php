<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('b2b_catalogs') && ! Schema::hasColumn('b2b_catalogs', 'type')) {
            Schema::table('b2b_catalogs', function (Blueprint $table) {
                $table->string('type', 20)->default('pdf')->after('title'); // pdf or google_sheet
                $table->string('google_sheet_url')->nullable()->after('pdf_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('b2b_catalogs') && Schema::hasColumn('b2b_catalogs', 'type')) {
            Schema::table('b2b_catalogs', function (Blueprint $table) {
                $table->dropColumn(['type', 'google_sheet_url']);
            });
        }
    }
};
