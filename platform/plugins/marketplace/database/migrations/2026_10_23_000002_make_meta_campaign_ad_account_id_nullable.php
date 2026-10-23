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

        $this->dropForeignKeyIfExists('meta_campaigns', 'ad_account_id');

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->unsignedBigInteger('ad_account_id')->nullable()->change();
        });

        $this->dropForeignKeyIfExists('meta_campaigns', 'ad_account_id');

        // Must happen BEFORE adding the FK below - any existing row whose
        // ad_account_id doesn't match a real meta_ad_accounts row (the
        // impossible sentinel value 0 included) would otherwise block the
        // constraint from being added at all. NULL correctly represents
        // "not yet linked to an account".
        DB::statement(
            'UPDATE meta_campaigns
             LEFT JOIN meta_ad_accounts ON meta_ad_accounts.id = meta_campaigns.ad_account_id
             SET meta_campaigns.ad_account_id = NULL
             WHERE meta_campaigns.ad_account_id IS NOT NULL AND meta_ad_accounts.id IS NULL'
        );

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('meta_campaigns') || ! Schema::hasColumn('meta_campaigns', 'ad_account_id')) {
            return;
        }

        $this->dropForeignKeyIfExists('meta_campaigns', 'ad_account_id');

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->unsignedBigInteger('ad_account_id')->nullable(false)->change();
        });

        Schema::table('meta_campaigns', function (Blueprint $table): void {
            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->cascadeOnDelete();
        });
    }

    /**
     * dropForeign() assumes the constraint exists and fatals (1091) if it
     * doesn't - which turned out to be the case on at least one real
     * deployment of this table, despite the original create-table migration
     * declaring one. Look it up in information_schema first instead of
     * guessing the constraint exists.
     */
    protected function dropForeignKeyIfExists(string $table, string $column): void
    {
        $connection = Schema::getConnection()->getDatabaseName();

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$connection, $table, $column]
        );

        foreach ($constraints as $constraint) {
            Schema::table($table, function (Blueprint $blueprintTable) use ($constraint): void {
                $blueprintTable->dropForeign($constraint->CONSTRAINT_NAME);
            });
        }
    }
};
