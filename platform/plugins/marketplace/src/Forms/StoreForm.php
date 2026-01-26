<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\EmailFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Carbon\Carbon;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Forms\Concerns\HasLocationFields;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Forms\Concerns\HasSubmitButton;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Botble\Marketplace\Models\Store;

class StoreForm extends FormAbstract
{
    use HasLocationFields;
    use HasSubmitButton;

    public function setup(): void
    {
        Assets::addScriptsDirectly('vendor/core/plugins/marketplace/js/store.js');

        $this
            ->model(Store::class)
            ->setValidatorClass(StoreRequest::class)
            ->columns(6)
            ->template('core/base::forms.form-no-wrap')
            ->hasFiles()
            ->add('name', TextField::class, NameFieldOption::make()->required()->colspan(6))
            ->add(
                'slug',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content(view('plugins/marketplace::stores.partials.shop-url-field', ['store' => $this->getModel()])->render())
                    ->colspan(3)
            )
            ->add('email', EmailField::class, EmailFieldOption::make()->required()->colspan(3))
            ->add('phone', TextField::class, [
                'label' => trans('plugins/marketplace::store.forms.phone'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.phone_placeholder'),
                    'data-counter' => 15,
                ],
                'colspan' => 6,
            ])
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->colspan(6))
            ->add('content', EditorField::class, ContentFieldOption::make()->colspan(6))
            ->addLocationFields()
            ->add(
                'company',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.company'))
                    ->placeholder(trans('plugins/marketplace::store.forms.company_placeholder'))
                    ->maxLength(255)
                    ->colspan(3)
            )
            ->add(
                'tax_id',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.tax_id'))
                    ->colspan(3)
                    ->maxLength(255)
            )
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Logo'))
                    ->colspan(2)
            )
            ->add(
                'logo_square',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Square Logo'))
                    ->helperText(__('This logo will be used in some special cases. Such as checkout page.'))
                    ->colspan(2)
            )
            ->add(
                'cover_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Cover Image'))
                    ->colspan(2)
            )
            ->add(
                'sponsored_video_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 style="margin-top: 20px; margin-bottom: 10px; padding-top: 15px; border-top: 1px solid #eee;">' . __('Sponsored Video (Admin Only)') . '</h4>')
                    ->colspan(6)
            )
            ->add(
                'sponsored_video_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Sponsored Video URL'))
                    ->placeholder(__('Enter YouTube or Vimeo video URL'))
                    ->helperText(__('This video will be displayed as sponsored content on the vendor store page.'))
                    ->maxLength(500)
                    ->colspan(3)
            )
            ->add(
                'sponsored_video_expires_at',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(__('Video Expiry Date'))
                    ->defaultValue($this->getModel()->sponsored_video_expires_at ? BaseHelper::formatDate($this->getModel()->sponsored_video_expires_at) : '')
                    ->helperText(__('Leave empty for no expiration. Video will be hidden after this date.'))
                    ->colspan(3)
            )
            ->add(
                'sponsored_video_thumbnail',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Sponsored Video Thumbnail'))
                    ->helperText(__('Thumbnail image for the sponsored video.'))
                    ->colspan(6)
            )
            ->add('status', SelectField::class, [
                'label' => trans('core/base::tables.status'),
                'required' => true,
                'choices' => BaseStatusEnum::labels(),
                'help_block' => [
                    TextField::class => trans('plugins/marketplace::marketplace.helpers.store_status', [
                        'customer' => CustomerStatusEnum::LOCKED()->label(),
                        'status' => BaseStatusEnum::PUBLISHED()->label(),
                    ]),
                ],
                'colspan' => 3,
            ])
            ->add('customer_id', SelectField::class, [
                'label' => trans('plugins/marketplace::store.forms.store_owner'),
                'required' => true,
                'choices' => [0 => trans('plugins/marketplace::store.forms.select_store_owner')] + Customer::query()
                    ->where('is_vendor', true)
                    ->pluck('name', 'id')
                    ->all(),
                'colspan' => 3,
            ])
            ->when(! MarketplaceHelper::hideStoreSocialLinks(), function (): void {
                $this
                    ->add('extended_info_content', HtmlField::class, [
                        'html' => view('plugins/marketplace::partials.extra-content', ['model' => $this->getModel()]),
                    ]);
            });
    }
}
