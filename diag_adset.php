<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== meta_ad_sets ===\n";
$rows = DB::table('meta_ad_sets')->get();
echo "Count: " . $rows->count() . "\n";
foreach ($rows as $r) {
    echo "  id={$r->id} store_id={$r->store_id} name={$r->name} optimization_goal={$r->optimization_goal} meta_adset_id=" . ($r->meta_adset_id ?? 'NULL') . " campaign_id={$r->campaign_id}\n";
}

echo "\n=== meta_campaigns ===\n";
$camps = DB::table('meta_campaigns')->get();
echo "Count: " . $camps->count() . "\n";
foreach ($camps as $c) {
    echo "  id={$c->id} store_id={$c->store_id} name={$c->name} objective={$c->objective} meta_campaign_id=" . ($c->meta_campaign_id ?? 'NULL') . "\n";
}

echo "\n=== meta_ad_accounts ===\n";
$accs = DB::table('meta_ad_accounts')->get();
foreach ($accs as $a) {
    echo "  id={$a->id} store_id={$a->store_id} ad_account_id={$a->ad_account_id} is_connected={$a->is_connected} fb_page_id=" . ($a->fb_page_id ?? 'NULL') . "\n";
}
