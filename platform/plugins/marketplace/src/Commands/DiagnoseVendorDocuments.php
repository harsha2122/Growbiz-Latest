<?php

namespace Botble\Marketplace\Commands;

use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DiagnoseVendorDocuments extends Command
{
    protected $signature = 'marketplace:diagnose-vendor-documents {--vendor-id= : Specific vendor ID to diagnose}';

    protected $description = 'Diagnose vendor document storage issues';

    public function handle(): int
    {
        $this->info('=== Vendor Document Diagnostics ===');
        $this->newLine();

        $storage = Storage::disk('local');
        $storagePath = $storage->path('');

        $this->info("Storage disk root: $storagePath");
        $this->info("Marketplace folder exists: " . ($storage->exists('marketplace') ? 'YES' : 'NO'));
        $this->newLine();

        // List all folders in marketplace
        if ($storage->exists('marketplace')) {
            $folders = $storage->directories('marketplace');
            $this->info("Folders in marketplace/: " . (count($folders) > 0 ? implode(', ', $folders) : 'NONE'));
        }
        $this->newLine();

        $vendorId = $this->option('vendor-id');

        if ($vendorId) {
            $vendors = Vendor::with('store')->where('id', $vendorId)->get();
        } else {
            $vendors = Vendor::with('store')->get();
        }

        foreach ($vendors as $vendor) {
            $this->info("--- Vendor: {$vendor->name} (ID: {$vendor->id}) ---");
            $this->line("  Is Vendor: " . ($vendor->is_vendor ? 'YES' : 'NO'));
            $this->line("  Verified At: " . ($vendor->vendor_verified_at ?? 'NOT VERIFIED'));

            $store = $vendor->store;

            if (!$store) {
                $this->error("  Store: NOT FOUND!");
                $this->newLine();
                continue;
            }

            $this->line("  Store ID: {$store->id}");
            $this->line("  Store Name: {$store->name}");
            $this->line("  Store Slug: " . ($store->slug ?: 'EMPTY'));

            // Check expected storage path
            $expectedPath = "marketplace/" . ($store->slug ?: $store->id);
            $this->line("  Expected Storage Path: $expectedPath");
            $this->line("  Path Exists: " . ($storage->exists($expectedPath) ? 'YES' : 'NO'));

            $this->newLine();
            $this->line("  Document Fields in Database:");

            // Check each document field
            $documents = [
                'pan_card_file' => $store->pan_card_file,
                'aadhar_card_file' => $store->aadhar_card_file,
                'gst_certificate_file' => $store->gst_certificate_file,
                'udyam_aadhar_file' => $store->udyam_aadhar_file,
            ];

            foreach ($documents as $field => $value) {
                if ($value) {
                    $fileExists = $storage->exists($value);
                    $fullPath = $storage->path($value);
                    $status = $fileExists ? '✓ EXISTS' : '✗ MISSING';
                    $this->line("    $field:");
                    $this->line("      DB Value: $value");
                    $this->line("      Full Path: $fullPath");
                    $this->line("      Status: $status");

                    if (!$fileExists) {
                        // Check if file exists with different extension
                        $basePath = preg_replace('/\.[^.]+$/', '', $value);
                        foreach (['pdf', 'jpg', 'jpeg', 'png'] as $ext) {
                            $altPath = "$basePath.$ext";
                            if ($storage->exists($altPath)) {
                                $this->warn("      Found alternate: $altPath");
                            }
                        }
                    }
                } else {
                    $this->line("    $field: NULL/EMPTY in database");
                }
            }

            // List actual files in the store's folder
            if ($storage->exists($expectedPath)) {
                $files = $storage->files($expectedPath);
                $this->newLine();
                $this->line("  Actual Files in $expectedPath:");
                if (count($files) > 0) {
                    foreach ($files as $file) {
                        $this->line("    - $file");
                    }
                } else {
                    $this->line("    NONE");
                }
            }

            $this->newLine();
        }

        $this->info('=== Diagnostics Complete ===');

        return self::SUCCESS;
    }
}
