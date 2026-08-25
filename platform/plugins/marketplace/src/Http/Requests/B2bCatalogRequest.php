<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class B2bCatalogRequest extends Request
{
    public function rules(): array
    {
        $isEdit = (bool) $this->route('b2b_catalog');
        $isPdfType = $this->input('type', 'pdf') === 'pdf';
        $hasExistingPdfs = $isEdit && $this->route('b2b_catalog')->pdfs()->exists();

        return [
            'title'               => ['required', 'string', 'max:255'],
            'type'                => ['required', 'in:pdf,google_sheet'],
            'description'         => ['nullable', 'string', 'max:2000'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'store_id'            => ['nullable', 'integer', 'exists:mp_stores,id'],
            'contact_number'      => ['nullable', 'string', 'max:20'],
            'whatsapp_number'     => ['nullable', 'string', 'max:20'],

            'google_sheet_url'    => [$isPdfType ? 'nullable' : 'required', 'nullable', 'url', 'max:2000'],

            'pdf_files'           => [$isPdfType && ! $isEdit && ! $hasExistingPdfs ? 'required' : 'nullable', 'array', 'min:1'],
            'pdf_files.*'         => [$isPdfType ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:524288'],
            'pdf_titles'          => [$isPdfType && ! $isEdit && ! $hasExistingPdfs ? 'required' : 'nullable', 'array'],
            'pdf_titles.*'        => [$isPdfType ? 'required' : 'nullable', 'string', 'max:255'],

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
