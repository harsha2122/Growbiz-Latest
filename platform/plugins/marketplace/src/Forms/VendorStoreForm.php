<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\EmailFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Marketplace\Forms\Fields\CustomEditorField;
use Botble\Marketplace\Http\Requests\Fronts\VendorStoreRequest;

class VendorStoreForm extends StoreForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(VendorStoreRequest::class)
            ->modify('content', CustomEditorField::class)
            ->remove(['status', 'customer_id', 'sponsored_video_section', 'sponsored_video_url', 'sponsored_video_expires_at', 'sponsored_video_thumbnail'])
            // Disable name, email, and phone fields - only admin can change these
            ->modify('name', TextField::class, NameFieldOption::make()->required()->colspan(6)->addAttribute('disabled', 'disabled'))
            ->modify('email', EmailField::class, EmailFieldOption::make()->required()->colspan(3)->addAttribute('disabled', 'disabled'))
            ->modify('phone', TextField::class, [
                'label' => trans('plugins/marketplace::store.forms.phone'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.phone_placeholder'),
                    'data-counter' => 15,
                    'disabled' => 'disabled',
                ],
                'colspan' => 6,
            ])
            // Visible but not editable - only admin can change the establishment date
            ->modify(
                'establishment_date',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(__('Business Establishment Date'))
                    ->defaultValue($this->getModel()->establishment_date ? BaseHelper::formatDate($this->getModel()->establishment_date) : '')
                    ->colspan(3)
                    ->disabled()
            );
    }
}
