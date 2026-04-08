<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_ad_sets', function (Blueprint $table) {
            if (! Schema::hasColumn('meta_ad_sets', 'bid_cap')) {
                $table->decimal('bid_cap', 10, 2)->nullable()->after('daily_budget');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meta_ad_sets', function (Blueprint $table) {
            if (Schema::hasColumn('meta_ad_sets', 'bid_cap')) {
                $table->dropColumn('bid_cap');
            }
        });
    }
};
