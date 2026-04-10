<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('b2b_catalogs', function (Blueprint $table) {
            if (! Schema::hasColumn('b2b_catalogs', 'contact_number')) {
                $table->string('contact_number', 20)->nullable()->after('pdf_path');
            }
            if (! Schema::hasColumn('b2b_catalogs', 'whatsapp_number')) {
                $table->string('whatsapp_number', 20)->nullable()->after('contact_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('b2b_catalogs', function (Blueprint $table) {
            $table->dropColumn(['contact_number', 'whatsapp_number']);
        });
    }
};
