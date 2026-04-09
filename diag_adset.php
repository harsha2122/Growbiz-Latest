<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Check pending migrations
echo "=== Pending Migrations ===\n";
$ran = DB::table('migrations')->pluck('migration')->toArray();
$files = glob(__DIR__ . '/platform/plugins/marketplace/database/migrations/*.php');
foreach ($files as $file) {
    $name = basename($file, '.php');
    if (!in_array($name, $ran)) {
        echo "  PENDING: $name\n";
    }
}
echo "\n";

// Check columns exist
echo "=== meta_campaigns columns ===\n";
$cols = Schema::getColumnListing('meta_campaigns');
echo implode(', ', $cols) . "\n\n";

echo "=== meta_ad_sets columns ===\n";
$cols = Schema::getColumnListing('meta_ad_sets');
echo implode(', ', $cols) . "\n\n";

echo "=== Row counts ===\n";
echo "meta_campaigns: " . DB::table('meta_campaigns')->count() . "\n";
echo "meta_ad_sets: " . DB::table('meta_ad_sets')->count() . "\n";
echo "meta_ads: " . DB::table('meta_ads')->count() . "\n";
