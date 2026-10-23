<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // WhatsApp-gate enquiries only collect name + phone, so email/address
        // can no longer be guaranteed present.
        Schema::table('mp_enquiries', function (Blueprint $table): void {
            $table->string('email')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mp_enquiries', function (Blueprint $table): void {
            $table->string('email')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }
};
