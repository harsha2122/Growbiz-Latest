<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->string('sponsored_video_url')->nullable();
            $table->string('sponsored_video_thumbnail')->nullable();
            $table->timestamp('sponsored_video_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            $table->dropColumn(['sponsored_video_url', 'sponsored_video_thumbnail', 'sponsored_video_expires_at']);
        });
    }
};
