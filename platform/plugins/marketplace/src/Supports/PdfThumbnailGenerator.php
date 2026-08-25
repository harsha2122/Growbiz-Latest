<?php

namespace Botble\Marketplace\Supports;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Renders the first page of an uploaded PDF as a small JPEG thumbnail, the same way
 * WhatsApp/link previews show a preview of a shared document, so B2B catalog cards can
 * show a glimpse of the PDF instead of a generic icon. Requires the "pdftoppm" binary
 * (poppler-utils); returns null (falling back to the icon in the view) when it isn't
 * available on the server or generation fails.
 */
class PdfThumbnailGenerator
{
    public static function generate(string $pdfPath): ?string
    {
        static $pdftoppmAvailable;
        $pdftoppmAvailable ??= (new ExecutableFinder())->find('pdftoppm') !== null;

        if (! $pdftoppmAvailable) {
            return null;
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('b2b-catalogs/thumbnails');

        $prefix = 'b2b-catalogs/thumbnails/' . Str::random(40);

        $process = new Process([
            'pdftoppm', '-jpeg', '-f', '1', '-l', '1',
            '-scale-to-x', '640', '-scale-to-y', '-1',
            '-jpegopt', 'quality=82',
            $disk->path($pdfPath), $disk->path($prefix),
        ]);
        $process->setTimeout(30);

        try {
            $process->run();
        } catch (\Throwable) {
            return null;
        }

        if (! $process->isSuccessful()) {
            return null;
        }

        $generated = glob($disk->path($prefix) . '*.jpg');

        if (empty($generated)) {
            return null;
        }

        return 'b2b-catalogs/thumbnails/' . basename($generated[0]);
    }

    public static function isAvailable(): bool
    {
        return (new ExecutableFinder())->find('pdftoppm') !== null;
    }
}
