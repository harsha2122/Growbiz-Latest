<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mp_stores') && ! Schema::hasColumn('mp_stores', 'whatsapp_number')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->string('whatsapp_number')->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mp_stores') && Schema::hasColumn('mp_stores', 'whatsapp_number')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->dropColumn('whatsapp_number');
            });
        }
    }
};
