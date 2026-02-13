<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\B2bCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:524288'],
        ]);

        $data = $request->only(['title', 'description']);
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
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:524288'],
        ]);

        $data = $request->only(['title', 'description']);

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
