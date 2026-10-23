<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * meta_campaigns.ad_account_id was created as a NOT NULL foreign key to
     * meta_ad_accounts.id, but the controller has always inserted a literal 0
     * for it (there being no "unconnected" sentinel row) - since no
     * meta_ad_accounts row can ever have id = 0, every single campaign create
     * has been throwing a foreign key constraint violation. Making the column
     * nullable (for the genuinely-supported "save as draft before connecting
     * Facebook" case) and re-adding the FK to allow NULL fixes this.
     */
    public function up(): void
    {
        if (! Schema::hasTable('meta_campaigns') || ! Schema::hasColumn('meta_campaigns', 'ad_account_id')) {
            return;
        }

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->dropForeign(['ad_account_id']);
        });

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->unsignedBigInteger('ad_account_id')->nullable()->change();
        });

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->nullOnDelete();
        });

        // Any existing rows with the impossible sentinel value 0 need clearing
        // to NULL, otherwise they'd now just be silently orphaned instead of
        // blocked - NULL correctly represents "not yet linked to an account".
        DB::table('meta_campaigns')->where('ad_account_id', 0)->update(['ad_account_id' => null]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('meta_campaigns') || ! Schema::hasColumn('meta_campaigns', 'ad_account_id')) {
            return;
        }

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->dropForeign(['ad_account_id']);
        });

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->unsignedBigInteger('ad_account_id')->nullable(false)->change();
        });

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->cascadeOnDelete();
        });
    }
};
