<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->string('aadhar_file_1')->nullable()->after('udyam_aadhar_file');
            $table->string('aadhar_file_2')->nullable()->after('aadhar_file_1');
            $table->string('business_doc_type')->nullable()->after('aadhar_file_2');
            $table->string('business_doc_file')->nullable()->after('business_doc_type');
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->dropColumn(['aadhar_file_1', 'aadhar_file_2', 'business_doc_type', 'business_doc_file']);
        });
    }
};
