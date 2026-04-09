<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$adSet = \Botble\Marketplace\Models\MetaAdSet::with('campaign')->find(7);
echo "Ad Set:\n";
echo "  optimization_goal: " . $adSet->optimization_goal . "\n";
echo "  campaign_id (local): " . $adSet->campaign_id . "\n";

$campaign = $adSet->campaign;
echo "\nCampaign:\n";
echo "  objective: " . $campaign->objective . "\n";
echo "  meta_campaign_id: " . $campaign->meta_campaign_id . "\n";
echo "  status: " . $campaign->status . "\n";
