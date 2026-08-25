<?php

namespace Botble\Marketplace\Commands;

use Botble\Marketplace\Models\B2bCatalogPdf;
use Botble\Marketplace\Supports\PdfThumbnailGenerator;
use Illuminate\Console\Command;

class GenerateB2bCatalogThumbnailsCommand extends Command
{
    protected $signature = 'b2b-catalogs:generate-thumbnails';
    protected $description = 'Backfill preview thumbnails for existing B2B catalog PDFs uploaded before the thumbnail feature existed';

    public function handle(): int
    {
        if (! PdfThumbnailGenerator::isAvailable()) {
            $this->error('The "pdftoppm" binary (poppler-utils) is not installed on this server. Install it first, e.g.: apt-get install -y poppler-utils');

            return self::FAILURE;
        }

        // Only the first PDF (lowest sort_order) of each catalog needs a thumbnail -
        // that's the only one shown on the catalog card.
        $firstPdfIds = B2bCatalogPdf::query()
            ->selectRaw('MIN(id) as id')
            ->groupBy('b2b_catalog_id')
            ->pluck('id');

        $pdfs = B2bCatalogPdf::query()
            ->whereIn('id', $firstPdfIds)
            ->whereNull('thumbnail_path')
            ->get();

        if ($pdfs->isEmpty()) {
            $this->info('Nothing to backfill — every catalog already has a thumbnail.');

            return self::SUCCESS;
        }

        $this->info("Generating thumbnails for {$pdfs->count()} catalog(s)...");

        $generated = 0;

        foreach ($pdfs as $pdf) {
            $thumbnail = PdfThumbnailGenerator::generate($pdf->pdf_path);

            if ($thumbnail) {
                $pdf->update(['thumbnail_path' => $thumbnail]);
                $generated++;
                $this->line("  ✓ Catalog #{$pdf->b2b_catalog_id}: thumbnail generated");
            } else {
                $this->warn("  ✗ Catalog #{$pdf->b2b_catalog_id}: could not generate thumbnail (PDF #{$pdf->id})");
            }
        }

        $this->info("Done. {$generated}/{$pdfs->count()} thumbnail(s) generated.");

        return self::SUCCESS;
    }
}
