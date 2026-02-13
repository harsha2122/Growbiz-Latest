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
            'pdf_file' => [$this->route('b2b_catalog') ? 'nullable' : 'required', 'file', 'mimes:pdf'],
        ];
    }
}
