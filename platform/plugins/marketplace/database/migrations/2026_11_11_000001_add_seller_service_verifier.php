<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_service_completions', fn (Blueprint $table) => $table->unsignedBigInteger('verified_by_seller')->nullable());
    }

    public function down(): void
    {
        Schema::table('mp_service_completions', fn (Blueprint $table) => $table->dropColumn('verified_by_seller'));
    }
};
