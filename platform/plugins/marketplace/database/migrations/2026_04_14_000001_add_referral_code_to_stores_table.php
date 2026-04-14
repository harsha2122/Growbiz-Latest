<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            if (! Schema::hasColumn('mp_stores', 'referral_code')) {
                $table->string('referral_code', 12)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            if (Schema::hasColumn('mp_stores', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
        });
    }
};
