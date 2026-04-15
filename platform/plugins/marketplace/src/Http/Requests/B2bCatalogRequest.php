<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class B2bCatalogRequest extends Request
{
    public function rules(): array
    {
        $isEdit = (bool) $this->route('b2b_catalog');

        return [
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:2000'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'store_id'            => ['nullable', 'integer', 'exists:mp_stores,id'],
            'contact_number'      => ['nullable', 'string', 'max:20'],
            'whatsapp_number'     => ['nullable', 'string', 'max:20'],

            'pdf_files'           => [$isEdit ? 'nullable' : 'required', 'array', 'min:1'],
            'pdf_files.*'         => ['required', 'file', 'mimes:pdf', 'max:524288'],
            'pdf_titles'          => [$isEdit ? 'nullable' : 'required', 'array'],
            'pdf_titles.*'        => ['required', 'string', 'max:255'],

            'new_pdf_titles'      => ['nullable', 'array'],
            'new_pdf_titles.*'    => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'pdf_files.*.max' => __('Each PDF file must not be larger than 512MB.'),
        ];
    }
}
