<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->unsignedInteger('happy_customers')->nullable();
            $table->string('public_location', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            $table->dropColumn(['years_of_experience', 'happy_customers', 'public_location']);
        });
    }
};
