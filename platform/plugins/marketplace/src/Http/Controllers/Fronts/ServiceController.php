<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\Fronts\VendorServiceRequest;
use Botble\Marketplace\Models\Service;
use Botble\Media\Facades\RvMedia;

class ServiceController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Services'));

        $store = auth('customer')->user()->store;

        $services = $store->services()->withCount('clicks')->paginate(20);

        return MarketplaceHelper::view('vendor-dashboard.services.index', compact('services'));
    }

    public function create()
    {
        $this->pageTitle(__('Add Service'));

        return MarketplaceHelper::view('vendor-dashboard.services.create');
    }

    public function store(VendorServiceRequest $request)
    {
        $store = auth('customer')->user()->store;

        $data = $request->only(['name', 'description', 'price', 'order']);
        $data['store_id'] = $store->getKey();
        $data['status'] = BaseStatusEnum::PUBLISHED;

        if ($request->hasFile('image_input')) {
            $result = RvMedia::handleUpload($request->file('image_input'), 0, $store->upload_folder);

            if (! $result['error']) {
                $data['image'] = $result['data']->url;
            }
        }

        Service::query()->create($data);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.services.index'))
            ->withCreatedSuccessMessage();
    }

    public function edit(int|string $id)
    {
        $service = $this->findOwnedService($id);

        $this->pageTitle(__('Edit Service: :name', ['name' => $service->name]));

        return MarketplaceHelper::view('vendor-dashboard.services.edit', compact('service'));
    }

    public function update(int|string $id, VendorServiceRequest $request)
    {
        $service = $this->findOwnedService($id);

        $data = $request->only(['name', 'description', 'price', 'order', 'status']);

        if ($request->hasFile('image_input')) {
            $result = RvMedia::handleUpload($request->file('image_input'), 0, $service->store->upload_folder);

            if (! $result['error']) {
                $data['image'] = $result['data']->url;
            }
        }

        $service->update($data);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.services.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int|string $id)
    {
        $service = $this->findOwnedService($id);

        $service->delete();

        return $this
            ->httpResponse()
            ->setMessage(__('Service deleted successfully.'));
    }

    protected function findOwnedService(int|string $id): Service
    {
        $store = auth('customer')->user()->store;

        return Service::query()
            ->where('store_id', $store->getKey())
            ->findOrFail($id);
    }
}
