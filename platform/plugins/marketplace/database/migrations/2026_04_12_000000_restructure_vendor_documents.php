<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            $table->string('aadhar_file_1')->nullable()->after('udyam_aadhar_file');
            $table->string('aadhar_file_2')->nullable()->after('aadhar_file_1');
            $table->string('business_doc_type')->nullable()->after('aadhar_file_2');
            $table->string('business_doc_file')->nullable()->after('business_doc_type');

            // Make old columns nullable for backward compatibility
            $table->string('pan_card_file')->nullable()->change();
            $table->string('aadhar_card_file')->nullable()->change();
            $table->string('gst_certificate_file')->nullable()->change();
            $table->string('udyam_aadhar_file')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            $table->dropColumn(['aadhar_file_1', 'aadhar_file_2', 'business_doc_type', 'business_doc_file']);
        });
    }
};
