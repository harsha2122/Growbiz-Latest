<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mp_service_clicks');
        Schema::dropIfExists('mp_services');
    }

    public function down(): void
    {
        // Intentionally left blank - the standalone services module was replaced
        // by service-type products, so this migration is not reversible.
    }
};
