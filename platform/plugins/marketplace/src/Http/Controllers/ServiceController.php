<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Forms\ServiceForm;
use Botble\Marketplace\Http\Requests\ServiceRequest;
use Botble\Marketplace\Models\Service;
use Botble\Marketplace\Tables\ServiceTable;
use Illuminate\Http\Request;

class ServiceController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(__('Services'), route('marketplace.services.index'));
    }

    public function index(ServiceTable $table)
    {
        $this->pageTitle(__('Services'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(__('Create Service'));

        return ServiceForm::create()->renderForm();
    }

    public function store(ServiceRequest $request)
    {
        $service = Service::query()->create($request->validated());

        event(new CreatedContentEvent(SERVICE_MODULE_SCREEN_NAME, $request, $service));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.services.index'))
            ->setNextUrl(route('marketplace.services.edit', $service->id))
            ->withCreatedSuccessMessage();
    }

    public function edit(int|string $id)
    {
        $service = Service::query()->findOrFail($id);

        $this->pageTitle(__('Edit Service: :name', ['name' => $service->name]));

        return ServiceForm::createFromModel($service)->renderForm();
    }

    public function update(int|string $id, ServiceRequest $request)
    {
        $service = Service::query()->findOrFail($id);

        $service->update($request->validated());

        event(new UpdatedContentEvent(SERVICE_MODULE_SCREEN_NAME, $request, $service));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.services.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int|string $id, Request $request)
    {
        $service = Service::query()->findOrFail($id);

        return DeleteResourceAction::make($service);
    }
}
