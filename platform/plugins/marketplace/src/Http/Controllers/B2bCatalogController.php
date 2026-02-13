<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Forms\B2bCatalogForm;
use Botble\Marketplace\Http\Requests\B2bCatalogRequest;
use Botble\Marketplace\Models\B2bCatalog;
use Botble\Marketplace\Tables\B2bCatalogTable;
use Illuminate\Support\Facades\Storage;

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

        return B2bCatalogForm::create()->renderForm();
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
            ->setPreviousRoute('marketplace.b2b-catalogs.index')
            ->setNextRoute('marketplace.b2b-catalogs.edit', $catalog->id)
            ->withCreatedSuccessMessage();
    }

    public function edit(B2bCatalog $b2b_catalog)
    {
        $this->pageTitle(__('Edit B2B Catalog: :title', ['title' => $b2b_catalog->title]));

        return B2bCatalogForm::createFromModel($b2b_catalog)->renderForm();
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
            ->setPreviousRoute('marketplace.b2b-catalogs.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(B2bCatalog $b2b_catalog): DeleteResourceAction
    {
        if ($b2b_catalog->pdf_path && Storage::disk('public')->exists($b2b_catalog->pdf_path)) {
            Storage::disk('public')->delete($b2b_catalog->pdf_path);
        }

        return DeleteResourceAction::make($b2b_catalog);
    }
}
