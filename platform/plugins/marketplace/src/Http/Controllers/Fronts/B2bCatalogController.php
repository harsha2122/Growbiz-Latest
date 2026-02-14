<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\B2bCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class B2bCatalogController extends BaseController
{
    public function __construct()
    {
        abort_unless(
            auth('customer')->check() && auth('customer')->user()->store?->is_key_account,
            403
        );
    }

    public function index()
    {
        $this->pageTitle(__('B2B Catalogs'));

        $catalogs = B2bCatalog::query()->latest()->paginate(20);

        return MarketplaceHelper::view('vendor-dashboard.b2b-catalogs.index', compact('catalogs'));
    }

    public function create()
    {
        $this->pageTitle(__('Add B2B Catalog'));

        return MarketplaceHelper::view('vendor-dashboard.b2b-catalogs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:524288'],
        ]);

        $data = $request->only(['title', 'description', 'discount_percentage']);
        $data['pdf_path'] = $request->file('pdf_file')->store('b2b-catalogs', 'public');
        $data['uploaded_by'] = auth('customer')->id();
        $data['uploaded_by_type'] = 'vendor';

        B2bCatalog::query()->create($data);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.b2b-catalogs.index'))
            ->withCreatedSuccessMessage();
    }

    public function edit($id)
    {
        $catalog = B2bCatalog::query()->findOrFail($id);

        $this->pageTitle(__('Edit B2B Catalog: :title', ['title' => $catalog->title]));

        return MarketplaceHelper::view('vendor-dashboard.b2b-catalogs.edit', compact('catalog'));
    }

    public function update($id, Request $request)
    {
        $catalog = B2bCatalog::query()->findOrFail($id);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:524288'],
        ]);

        $data = $request->only(['title', 'description', 'discount_percentage']);

        if ($request->hasFile('pdf_file')) {
            if ($catalog->pdf_path && Storage::disk('public')->exists($catalog->pdf_path)) {
                Storage::disk('public')->delete($catalog->pdf_path);
            }
            $data['pdf_path'] = $request->file('pdf_file')->store('b2b-catalogs', 'public');
        }

        $catalog->update($data);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.b2b-catalogs.index'))
            ->withUpdatedSuccessMessage();
    }

    public function viewPdf($id)
    {
        $catalog = B2bCatalog::query()->findOrFail($id);

        abort_unless(
            $catalog->pdf_path && Storage::disk('public')->exists($catalog->pdf_path),
            404
        );

        return MarketplaceHelper::view('vendor-dashboard.b2b-catalogs.view-pdf', [
            'catalog' => $catalog,
            'streamUrl' => route('marketplace.vendor.b2b-catalogs.stream-pdf', $catalog->id),
        ]);
    }

    public function streamPdf($id, Request $request): StreamedResponse
    {
        $catalog = B2bCatalog::query()->findOrFail($id);

        abort_unless(
            $catalog->pdf_path && Storage::disk('public')->exists($catalog->pdf_path),
            404
        );

        $disk = Storage::disk('public');
        $path = $catalog->pdf_path;
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

    public function destroy($id)
    {
        $catalog = B2bCatalog::query()->findOrFail($id);

        if ($catalog->pdf_path && Storage::disk('public')->exists($catalog->pdf_path)) {
            Storage::disk('public')->delete($catalog->pdf_path);
        }

        $catalog->delete();

        return $this
            ->httpResponse()
            ->setMessage(__('Deleted successfully.'));
    }
}
