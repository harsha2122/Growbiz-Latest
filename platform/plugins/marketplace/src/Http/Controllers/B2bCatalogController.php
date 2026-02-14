<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Http\Requests\B2bCatalogRequest;
use Botble\Marketplace\Models\B2bCatalog;
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

        return view('plugins/marketplace::b2b-catalogs.create');
    }

    public function store(B2bCatalogRequest $request)
    {
        $data = $request->only(['title', 'description']);

        if ($request->hasFile('pdf_file')) {
            $data['pdf_path'] = $request->file('pdf_file')->store('b2b-catalogs', 'public');
        }

        $data['uploaded_by'] = auth()->id();
        $data['uploaded_by_type'] = 'admin';

        $catalog = B2bCatalog::query()->create($data);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.b2b-catalogs.index'))
            ->withCreatedSuccessMessage();
    }

    public function edit(B2bCatalog $b2b_catalog)
    {
        $this->pageTitle(__('Edit B2B Catalog: :title', ['title' => $b2b_catalog->title]));

        return view('plugins/marketplace::b2b-catalogs.edit', ['catalog' => $b2b_catalog]);
    }

    public function update(B2bCatalog $b2b_catalog, B2bCatalogRequest $request)
    {
        $data = $request->only(['title', 'description']);

        if ($request->hasFile('pdf_file')) {
            if ($b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path)) {
                Storage::disk('public')->delete($b2b_catalog->pdf_path);
            }
            $data['pdf_path'] = $request->file('pdf_file')->store('b2b-catalogs', 'public');
        }

        $b2b_catalog->update($data);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.b2b-catalogs.index'))
            ->withUpdatedSuccessMessage();
    }

    public function viewPdf(B2bCatalog $b2b_catalog)
    {
        abort_unless(
            $b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path),
            404
        );

        return view('plugins/marketplace::b2b-catalogs.view-pdf', [
            'catalog' => $b2b_catalog,
            'streamUrl' => route('marketplace.b2b-catalogs.stream-pdf', $b2b_catalog->id),
        ]);
    }

    public function streamPdf(B2bCatalog $b2b_catalog, Request $request): StreamedResponse
    {
        abort_unless(
            $b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path),
            404
        );

        $disk = Storage::disk('public');
        $path = $b2b_catalog->pdf_path;
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
            $response->headers->set('Content-Length', $length);
            $response->headers->set('Accept-Ranges', 'bytes');

            return $response;
        }

        $response = new StreamedResponse(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            while (! feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Disposition', 'inline; filename="' . basename($path) . '"');
        $response->headers->set('Content-Length', $fileSize);
        $response->headers->set('Accept-Ranges', 'bytes');

        return $response;
    }

    public function destroy(B2bCatalog $b2b_catalog): DeleteResourceAction
    {
        if ($b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path)) {
            Storage::disk('public')->delete($b2b_catalog->pdf_path);
        }

        return DeleteResourceAction::make($b2b_catalog);
    }
}
