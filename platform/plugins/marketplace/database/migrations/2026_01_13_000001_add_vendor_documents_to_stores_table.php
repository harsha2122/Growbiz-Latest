<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->string('pan_card_file')->nullable();
            $table->string('aadhar_card_file')->nullable();
            $table->string('gst_certificate_file')->nullable();
            $table->string('udyam_aadhar_file')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->dropColumn(['pan_card_file', 'aadhar_card_file', 'gst_certificate_file', 'udyam_aadhar_file']);
        });
    }
};
