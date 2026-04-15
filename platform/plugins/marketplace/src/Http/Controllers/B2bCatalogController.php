<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Http\Requests\B2bCatalogRequest;
use Botble\Marketplace\Models\B2bCatalog;
use Botble\Marketplace\Models\B2bCatalogPdf;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Tables\B2bCatalogTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class B2bCatalogController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(__('Marketplace'))
            ->add(__('B2B Catalogs'), route('marketplace.b2b-catalogs.index'));
    }

    public function index(B2bCatalogTable $table)
    {
        $this->pageTitle(__('B2B Catalogs'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(__('Create B2B Catalog'));

        $stores = Store::query()->select('id', 'name')->orderBy('name')->get();

        return view('plugins/marketplace::b2b-catalogs.create', compact('stores'));
    }

    public function store(B2bCatalogRequest $request)
    {
        $data = $request->only(['title', 'description', 'discount_percentage', 'contact_number', 'whatsapp_number', 'store_id']);
        $data['uploaded_by'] = auth()->id();
        $data['uploaded_by_type'] = 'admin';

        $catalog = B2bCatalog::query()->create($data);

        foreach ($request->file('pdf_files', []) as $i => $file) {
            $path = $file->store('b2b-catalogs', 'public');
            $catalog->pdfs()->create([
                'title'      => $request->input("pdf_titles.$i"),
                'pdf_path'   => $path,
                'sort_order' => $i,
            ]);
        }

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.b2b-catalogs.index'))
            ->withCreatedSuccessMessage();
    }

    public function edit(B2bCatalog $b2b_catalog)
    {
        $this->pageTitle(__('Edit B2B Catalog: :title', ['title' => $b2b_catalog->title]));

        $stores = Store::query()->select('id', 'name')->orderBy('name')->get();

        return view('plugins/marketplace::b2b-catalogs.edit', [
            'catalog' => $b2b_catalog->load('pdfs'),
            'stores'  => $stores,
        ]);
    }

    public function update(B2bCatalog $b2b_catalog, B2bCatalogRequest $request)
    {
        $data = $request->only(['title', 'description', 'discount_percentage', 'contact_number', 'whatsapp_number', 'store_id']);

        $b2b_catalog->update($data);

        $maxOrder = $b2b_catalog->pdfs()->max('sort_order') ?? -1;

        foreach ($request->file('pdf_files', []) as $i => $file) {
            $path = $file->store('b2b-catalogs', 'public');
            $b2b_catalog->pdfs()->create([
                'title'      => $request->input("new_pdf_titles.$i"),
                'pdf_path'   => $path,
                'sort_order' => $maxOrder + $i + 1,
            ]);
        }

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.b2b-catalogs.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroyPdf(B2bCatalog $b2b_catalog, B2bCatalogPdf $b2b_catalog_pdf)
    {
        abort_unless($b2b_catalog_pdf->b2b_catalog_id === $b2b_catalog->id, 404);

        Storage::disk('public')->delete($b2b_catalog_pdf->pdf_path);
        $b2b_catalog_pdf->delete();

        return $this->httpResponse()->setMessage(__('PDF deleted successfully.'));
    }

    public function viewPdf(B2bCatalog $b2b_catalog)
    {
        abort_unless(
            $b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path),
            404
        );

        return view('plugins/marketplace::b2b-catalogs.view-pdf', [
            'catalog'   => $b2b_catalog,
            'streamUrl' => route('marketplace.b2b-catalogs.stream-pdf', $b2b_catalog->id),
        ]);
    }

    public function viewCatalogPdf(B2bCatalog $b2b_catalog, B2bCatalogPdf $b2b_catalog_pdf)
    {
        abort_unless($b2b_catalog_pdf->b2b_catalog_id === $b2b_catalog->id, 404);
        abort_unless(
            $b2b_catalog_pdf->pdf_path && Storage::disk('public')->exists($b2b_catalog_pdf->pdf_path),
            404
        );

        return view('plugins/marketplace::b2b-catalogs.view-pdf', [
            'catalog'   => $b2b_catalog,
            'streamUrl' => route('marketplace.b2b-catalogs.pdfs.stream', [$b2b_catalog->id, $b2b_catalog_pdf->id]),
        ]);
    }

    public function streamPdf(B2bCatalog $b2b_catalog, Request $request): StreamedResponse
    {
        abort_unless(
            $b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path),
            404
        );

        return $this->buildStreamResponse(Storage::disk('public'), $b2b_catalog->pdf_path, $request);
    }

    public function streamCatalogPdf(B2bCatalog $b2b_catalog, B2bCatalogPdf $b2b_catalog_pdf, Request $request): StreamedResponse
    {
        abort_unless($b2b_catalog_pdf->b2b_catalog_id === $b2b_catalog->id, 404);
        abort_unless(
            $b2b_catalog_pdf->pdf_path && Storage::disk('public')->exists($b2b_catalog_pdf->pdf_path),
            404
        );

        return $this->buildStreamResponse(Storage::disk('public'), $b2b_catalog_pdf->pdf_path, $request);
    }

    protected function buildStreamResponse($disk, string $path, Request $request): StreamedResponse
    {
        $fileSize = $disk->size($path);
        $mimeType = $disk->mimeType($path) ?: 'application/pdf';
        $rangeHeader = $request->header('Range');

        if ($rangeHeader) {
            preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches);
            $start = (int) $matches[1];
            $end = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $fileSize - 1;
            $end = min($end, $fileSize - 1);
            $length = $end - $start + 1;

            $response = new StreamedResponse(function () use ($disk, $path, $start, $length) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                $stream = $disk->readStream($path);
                fseek($stream, $start);
                $remaining = $length;
                while ($remaining > 0 && ! feof($stream)) {
                    $chunk = fread($stream, min(8192, $remaining));
                    echo $chunk;
                    flush();
                    $remaining -= strlen($chunk);
                }
                fclose($stream);
            }, 206);

            $response->headers->set('Content-Type', $mimeType);
            $response->headers->set('Content-Range', "bytes $start-$end/$fileSize");
            $response->headers->set('Content-Length', (string) $length);
            $response->headers->set('Accept-Ranges', 'bytes');
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');

            return $response;
        }

        $response = new StreamedResponse(function () use ($disk, $path) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            $stream = $disk->readStream($path);
            while (! feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Disposition', 'inline; filename="' . basename($path) . '"');
        $response->headers->set('Content-Length', (string) $fileSize);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');

        return $response;
    }

    public function destroy(B2bCatalog $b2b_catalog): DeleteResourceAction
    {
        foreach ($b2b_catalog->pdfs as $pdf) {
            Storage::disk('public')->delete($pdf->pdf_path);
        }

        if ($b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path)) {
            Storage::disk('public')->delete($b2b_catalog->pdf_path);
        }

        return DeleteResourceAction::make($b2b_catalog);
    }
}
