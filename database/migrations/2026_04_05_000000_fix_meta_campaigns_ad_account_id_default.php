<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Fix meta_campaigns.ad_account_id — add default 0 so it's not required on insert
        if (Schema::hasTable('meta_campaigns') && Schema::hasColumn('meta_campaigns', 'ad_account_id')) {
            Schema::table('meta_campaigns', function (Blueprint $table) {
                // Drop foreign key if it exists, then make column nullable with default
                try {
                    $table->dropForeign('meta_campaigns_ad_account_id_foreign');
                } catch (\Throwable $e) {
                    // Foreign key may not exist
                }
                $table->unsignedBigInteger('ad_account_id')->default(0)->change();
            });
        }
    }

    public function down(): void
    {
        //
    }
};
