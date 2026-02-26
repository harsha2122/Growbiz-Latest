<?php

namespace Botble\Marketplace\Http\Requests\Fronts;

use Botble\Support\Http\Requests\Request;

class StoreMetaAdRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ad_set_id' => ['required', 'integer', 'exists:meta_ad_sets,id'],
            'format' => ['required', 'string', 'in:SINGLE_IMAGE,CAROUSEL,VIDEO,COLLECTION'],
            'primary_text' => ['nullable', 'string', 'max:2000'],
            'headline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cta_button' => ['nullable', 'string', 'in:LEARN_MORE,SHOP_NOW,SIGN_UP,CONTACT_US,GET_OFFER,BOOK_NOW,SUBSCRIBE'],
            'destination_url' => ['nullable', 'url', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,PAUSED,IN_REVIEW'],
        ];
    }
}
