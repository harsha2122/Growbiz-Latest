<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mp_subscription_plans') && ! Schema::hasColumn('mp_subscription_plans', 'include_meta_ads')) {
            Schema::table('mp_subscription_plans', function (Blueprint $table) {
                // Defaults to true so existing plans (and vendors already using Meta
                // Ads before plan-based gating existed) keep access unless an admin
                // explicitly unchecks it for a given plan.
                $table->boolean('include_meta_ads')->default(true)->after('is_active');
            });
        }

        if (Schema::hasTable('mp_stores') && ! Schema::hasColumn('mp_stores', 'first_dashboard_visited_at')) {
            Schema::table('mp_stores', function (Blueprint $table) {
                $table->timestamp('first_dashboard_visited_at')->nullable()->after('is_verified');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mp_subscription_plans', 'include_meta_ads')) {
            Schema::table('mp_subscription_plans', fn (Blueprint $t) => $t->dropColumn('include_meta_ads'));
        }

        if (Schema::hasColumn('mp_stores', 'first_dashboard_visited_at')) {
            Schema::table('mp_stores', fn (Blueprint $t) => $t->dropColumn('first_dashboard_visited_at'));
        }
    }
};
