<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Rules\MediaImageRule;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\StoreBrand;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Request;

class StoreBrandController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Brands'));

        $store = auth('customer')->user()?->store;

        $brands = $store ? $store->brands()->latest('id')->get() : collect();

        return MarketplaceHelper::view('vendor-dashboard.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $store = auth('customer')->user()?->store;

        abort_if(! $store, 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo_input' => ['required', new MediaImageRule()],
        ]);

        $result = RvMedia::handleUpload($request->file('logo_input'), 0, $store->upload_folder);

        if ($result['error']) {
            return $this->httpResponse()->setError()->setMessage($result['message']);
        }

        StoreBrand::query()->create([
            'store_id' => $store->id,
            'name' => $request->input('name'),
            'logo' => $result['data']->url,
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.brands.index'))
            ->setMessage(__('Brand added successfully!'));
    }

    public function destroy(int|string $id)
    {
        $store = auth('customer')->user()?->store;

        $brand = StoreBrand::query()->where('store_id', $store?->id)->findOrFail($id);
        $brand->delete();

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.brands.index'))
            ->setMessage(__('Brand deleted successfully!'));
    }
}
