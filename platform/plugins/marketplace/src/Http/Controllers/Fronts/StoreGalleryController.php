<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Rules\MediaImageRule;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\StoreGalleryImage;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Request;

class StoreGalleryController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Gallery'));

        $store = auth('customer')->user()?->store;

        $images = $store ? $store->galleryImages()->get() : collect();
        $limit = $store?->galleryLimit() ?? 0;

        return MarketplaceHelper::view('vendor-dashboard.gallery.index', compact('images', 'limit'));
    }

    public function store(Request $request)
    {
        $store = auth('customer')->user()?->store;

        abort_if(! $store, 404);

        if (! $store->canAddGalleryImage()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(__('You have reached your gallery image limit of :limit. Remove an image to add a new one.', ['limit' => $store->galleryLimit()]));
        }

        $request->validate([
            'image_input' => ['required', new MediaImageRule()],
        ]);

        $result = RvMedia::handleUpload($request->file('image_input'), 0, $store->upload_folder);

        if ($result['error']) {
            return $this->httpResponse()->setError()->setMessage($result['message']);
        }

        StoreGalleryImage::query()->create([
            'store_id' => $store->id,
            'image' => $result['data']->url,
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.gallery.index'))
            ->setMessage(__('Image added successfully!'));
    }

    public function destroy(int|string $id)
    {
        $store = auth('customer')->user()?->store;

        $image = StoreGalleryImage::query()->where('store_id', $store?->id)->findOrFail($id);
        $image->delete();

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.gallery.index'))
            ->setMessage(__('Image deleted successfully!'));
    }
}
