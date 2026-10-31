<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ec_options', function (Blueprint $table): void {
            $table->boolean('is_warranty')->default(false)->index();
            $table->text('warranty_terms')->nullable();
            $table->string('included_warranty', 160)->nullable();
        });
        Schema::table('ec_option_value', function (Blueprint $table): void {
            $table->unsignedSmallInteger('warranty_months')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ec_option_value', fn (Blueprint $table) => $table->dropColumn('warranty_months'));
        Schema::table('ec_options', function (Blueprint $table): void {
            $table->dropIndex(['is_warranty']);
            $table->dropColumn(['is_warranty', 'warranty_terms', 'included_warranty']);
        });
    }
};
