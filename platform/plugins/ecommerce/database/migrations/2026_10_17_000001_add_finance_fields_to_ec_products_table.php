<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_products', function (Blueprint $table): void {
            if (! Schema::hasColumn('ec_products', 'finance_available')) {
                $table->boolean('finance_available')->default(false)->after('vendor_commission_type');
            }
            if (! Schema::hasColumn('ec_products', 'finance_provider')) {
                $table->string('finance_provider')->nullable()->after('finance_available');
            }
            if (! Schema::hasColumn('ec_products', 'finance_interest_rate')) {
                $table->decimal('finance_interest_rate', 5, 2)->nullable()->after('finance_provider');
            }
            if (! Schema::hasColumn('ec_products', 'finance_tenure_options')) {
                $table->string('finance_tenure_options')->nullable()->after('finance_interest_rate');
            }
            if (! Schema::hasColumn('ec_products', 'finance_down_payment_percent')) {
                $table->decimal('finance_down_payment_percent', 5, 2)->nullable()->after('finance_tenure_options');
            }
            if (! Schema::hasColumn('ec_products', 'finance_eligibility_notes')) {
                $table->text('finance_eligibility_notes')->nullable()->after('finance_down_payment_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ec_products', function (Blueprint $table): void {
            $table->dropColumn([
                'finance_available', 'finance_provider', 'finance_interest_rate',
                'finance_tenure_options', 'finance_down_payment_percent', 'finance_eligibility_notes',
            ]);
        });
    }
};
