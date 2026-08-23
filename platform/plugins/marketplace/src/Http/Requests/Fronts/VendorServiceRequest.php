<?php

namespace Botble\Marketplace\Http\Requests\Fronts;

use Botble\Base\Rules\MediaImageRule;
use Botble\Marketplace\Http\Requests\ServiceRequest;

class VendorServiceRequest extends ServiceRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // The vendor's own store is implicit (from the logged-in customer), not
        // client-selectable.
        unset($rules['store_id'], $rules['image']);

        // Vendors upload a raw file (handled via RvMedia::handleUpload in the controller),
        // rather than picking an already-uploaded media path via the admin media library
        // widget.
        $rules['image_input'] = ['nullable', new MediaImageRule()];

        return $rules;
    }
}
