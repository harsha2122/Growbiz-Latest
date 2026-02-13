<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class B2bCatalogRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'pdf_file' => [$this->route('b2b_catalog') ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'pdf_file.max' => __('The PDF file must not be larger than 50MB. Please also ensure PHP upload_max_filesize and post_max_size are set to at least 50M.'),
        ];
    }
}
