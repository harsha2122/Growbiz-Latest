<?php

namespace Botble\Marketplace\Commands;

use Botble\Marketplace\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncVendorDocuments extends Command
{
    protected $signature = 'marketplace:sync-vendor-documents';

    protected $description = 'Sync existing vendor document files to database';

    public function handle(): int
    {
        $this->info('Syncing vendor documents...');

        $storage = Storage::disk('local');
        $stores = Store::all();
        $synced = 0;

        foreach ($stores as $store) {
            $updated = false;
            $storeSlug = $store->slug ?: $store->id;
            $storagePath = "marketplace/$storeSlug";

            // Check if marketplace folder exists for this store
            if (!$storage->exists($storagePath)) {
                continue;
            }

            // Sync PAN Card
            if (!$store->pan_card_file) {
                $files = $storage->files($storagePath);
                foreach ($files as $file) {
                    if (preg_match('/pan_card\.(jpg|jpeg|png|pdf)$/i', $file)) {
                        $store->pan_card_file = $file;
                        $updated = true;
                        $this->line("  - Found PAN Card: $file");
                        break;
                    }
                }
            }

            // Sync Aadhar Card
            if (!$store->aadhar_card_file) {
                $files = $storage->files($storagePath);
                foreach ($files as $file) {
                    if (preg_match('/aadhar_card\.(jpg|jpeg|png|pdf)$/i', $file)) {
                        $store->aadhar_card_file = $file;
                        $updated = true;
                        $this->line("  - Found Aadhar Card: $file");
                        break;
                    }
                }
            }

            // Sync GST Certificate
            if (!$store->gst_certificate_file) {
                $files = $storage->files($storagePath);
                foreach ($files as $file) {
                    if (preg_match('/gst_certificate\.(jpg|jpeg|png|pdf)$/i', $file)) {
                        $store->gst_certificate_file = $file;
                        $updated = true;
                        $this->line("  - Found GST Certificate: $file");
                        break;
                    }
                }
            }

            // Sync Udyam Aadhar
            if (!$store->udyam_aadhar_file) {
                $files = $storage->files($storagePath);
                foreach ($files as $file) {
                    if (preg_match('/udyam_aadhar\.(jpg|jpeg|png|pdf)$/i', $file)) {
                        $store->udyam_aadhar_file = $file;
                        $updated = true;
                        $this->line("  - Found Udyam Aadhar: $file");
                        break;
                    }
                }
            }

            if ($updated) {
                $store->save();
                $synced++;
                $this->info("✓ Synced documents for: {$store->name}");
            }
        }

        $this->info("Completed! Synced documents for $synced store(s).");

        return self::SUCCESS;
    }
}
