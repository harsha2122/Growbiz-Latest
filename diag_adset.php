<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$adSets = \Botble\Marketplace\Models\MetaAdSet::with('campaign')->get();
foreach ($adSets as $adSet) {
    echo "Ad Set ID={$adSet->id}: name={$adSet->name}\n";
    echo "  optimization_goal: {$adSet->optimization_goal}\n";
    echo "  meta_adset_id: " . ($adSet->meta_adset_id ?? 'NULL') . "\n";
    $c = $adSet->campaign;
    if ($c) {
        echo "  campaign objective: {$c->objective}\n";
        echo "  campaign meta_campaign_id: " . ($c->meta_campaign_id ?? 'NULL') . "\n";
    }
    echo "\n";
}
