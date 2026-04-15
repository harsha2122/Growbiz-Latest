<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\B2bCatalog;
use Botble\Marketplace\Models\B2bCatalogPdf;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicB2bCatalogController extends BaseController
{
    public function index(Request $request)
    {
        $query = B2bCatalog::query()
            ->with(['store:id,name', 'pdfs'])
            ->where(function ($q) {
                $q->whereHas('pdfs')
                    ->orWhereNotNull('pdf_path');
            });

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        $catalogs = $query->latest()->paginate(12);

        return Theme::scope('marketplace.b2b-catalogs.index', compact('catalogs'))->render();
    }

    public function viewPdf(B2bCatalog $catalog, B2bCatalogPdf $pdf)
    {
        abort_unless($pdf->b2b_catalog_id === $catalog->id, 404);
        abort_unless(
            $pdf->pdf_path && Storage::disk('public')->exists($pdf->pdf_path),
            404
        );

        return Theme::scope('marketplace.b2b-catalogs.view-pdf', [
            'catalog'   => $catalog,
            'pdf'       => $pdf,
            'streamUrl' => route('public.b2b-catalogs.pdf.stream', [$catalog->id, $pdf->id]),
        ])->render();
    }

    public function streamPdf(B2bCatalog $catalog, B2bCatalogPdf $pdf, Request $request): StreamedResponse
    {
        abort_unless($pdf->b2b_catalog_id === $catalog->id, 404);
        abort_unless(
            $pdf->pdf_path && Storage::disk('public')->exists($pdf->pdf_path),
            404
        );

        return $this->buildStreamResponse(Storage::disk('public'), $pdf->pdf_path, $request);
    }

    protected function buildStreamResponse($disk, string $path, Request $request): StreamedResponse
    {
        $fileSize = $disk->size($path);
        $mimeType = $disk->mimeType($path) ?: 'application/pdf';
        $rangeHeader = $request->header('Range');

        if ($rangeHeader) {
            preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches);
            $start  = (int) $matches[1];
            $end    = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $fileSize - 1;
            $end    = min($end, $fileSize - 1);
            $length = $end - $start + 1;

            $response = new StreamedResponse(function () use ($disk, $path, $start, $length) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                $stream    = $disk->readStream($path);
                fseek($stream, $start);
                $remaining = $length;
                while ($remaining > 0 && ! feof($stream)) {
                    $chunk = fread($stream, min(8192, $remaining));
                    if ($chunk === false) {
                        break;
                    }
                    echo $chunk;
                    $remaining -= strlen($chunk);
                    flush();
                }
                fclose($stream);
            }, 206);

            $response->headers->set('Content-Type', $mimeType);
            $response->headers->set('Content-Range', "bytes $start-$end/$fileSize");
            $response->headers->set('Content-Length', $length);
            $response->headers->set('Accept-Ranges', 'bytes');

            return $response;
        }

        return new StreamedResponse(function () use ($disk, $path) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            $stream = $disk->readStream($path);
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type'   => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges'  => 'bytes',
        ]);
    }
}
