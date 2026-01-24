<?php

/**
 * Quick diagnostic script to check vendor document fields
 * Run: php check_vendor_docs.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Botble\Marketplace\Models\Store;
use Illuminate\Support\Facades\Storage;

echo "=== Vendor Document Diagnostic ===\n\n";

$stores = Store::with('customer')->get();

if ($stores->isEmpty()) {
    echo "No stores found in database.\n";
    exit;
}

foreach ($stores as $store) {
    $customer = $store->customer;
    echo "Store: {$store->name}\n";
    echo "Customer: {$customer->name} (ID: {$customer->id})\n";
    echo "Slug: {$store->slug}\n";
    echo "\nDatabase Fields:\n";
    echo "  - pan_card_file: " . ($store->pan_card_file ?: 'NULL') . "\n";
    echo "  - aadhar_card_file: " . ($store->aadhar_card_file ?: 'NULL') . "\n";
    echo "  - gst_certificate_file: " . ($store->gst_certificate_file ?: 'NULL') . "\n";
    echo "  - udyam_aadhar_file: " . ($store->udyam_aadhar_file ?: 'NULL') . "\n";

    echo "\nFiles on Disk:\n";
    $storage = Storage::disk('local');
    $storagePath = "marketplace/{$store->slug}";

    if ($storage->exists($storagePath)) {
        $files = $storage->files($storagePath);
        if (empty($files)) {
            echo "  (no files found)\n";
        } else {
            foreach ($files as $file) {
                $exists = $storage->exists($file) ? '✓' : '✗';
                echo "  {$exists} {$file}\n";
            }
        }
    } else {
        echo "  Directory does not exist: {$storagePath}\n";
    }

    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "Done!\n";
