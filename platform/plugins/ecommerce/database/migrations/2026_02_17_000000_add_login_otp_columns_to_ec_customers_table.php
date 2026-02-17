<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('ec_customers', 'login_otp')) {
            return;
        }

        Schema::table('ec_customers', function (Blueprint $table): void {
            $table->string('login_otp')->nullable();
            $table->timestamp('login_otp_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ec_customers', function (Blueprint $table): void {
            $table->dropColumn(['login_otp', 'login_otp_expires_at']);
        });
    }
};
