<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Http\Requests\ServiceRequest;
use Botble\Marketplace\Models\Service;
use Botble\Marketplace\Models\Store;

class ServiceForm extends FormAbstract
{
    public function setup(): void
    {
        $stores = Store::query()->wherePublished()->pluck('name', 'id')->all();

        $this
            ->model(Service::class)
            ->setValidatorClass(ServiceRequest::class)
            ->add(
                'store_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Vendor / Store'))
                    ->choices($stores)
                    ->searchable()
                    ->required()
            )
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('description', TextareaField::class, DescriptionFieldOption::make())
            ->add(
                'image',
                MediaImageField::class,
                MediaImageFieldOption::make()->label(__('Image'))
            )
            ->add(
                'price',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(__('Price'))
                    ->helperText(__('Leave empty to hide the price and show "Contact for price".'))
            )
            ->add(
                'order',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(__('Display Order'))
                    ->defaultValue(0)
            )
            ->add('status', SelectField::class, [
                'label' => trans('core/base::tables.status'),
                'required' => true,
                'choices' => BaseStatusEnum::labels(),
            ]);
    }
}
