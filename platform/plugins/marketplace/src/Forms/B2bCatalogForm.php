<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Http\Requests\B2bCatalogRequest;
use Botble\Marketplace\Models\B2bCatalog;

class B2bCatalogForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(B2bCatalog::class)
            ->setValidatorClass(B2bCatalogRequest::class)
            ->add('title', TextField::class, NameFieldOption::make()->label(__('Title'))->required())
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->label(__('Description')))
            ->add('pdf_file', 'file', [
                'label' => __('PDF File'),
                'attr' => [
                    'accept' => '.pdf',
                ],
                'help_block' => [
                    'text' => $this->getModel()?->pdf_path
                        ? __('Current file: :file. Upload a new file to replace it.', ['file' => basename($this->getModel()->pdf_path)])
                        : __('Upload a PDF file.'),
                ],
            ]);
    }
}
