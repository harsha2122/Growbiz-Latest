<?php

namespace Botble\Marketplace\Forms\Fronts;

use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\ButtonFieldOption;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\Fronts\BecomeVendorRequest;
use Botble\Theme\Facades\Theme;

class BecomeVendorForm extends FormAbstract
{
    public function setup(): void
    {
        Theme::asset()
            ->container('footer')
            ->add('marketplace-register', 'vendor/core/plugins/marketplace/js/customer-register.js', ['jquery']);

        Theme::asset()
            ->add('dropzone', 'vendor/core/core/base/libraries/dropzone/dropzone.css');

        Theme::asset()
            ->container('footer')
            ->add('dropzone', 'vendor/core/core/base/libraries/dropzone/dropzone.js');

        $this
            ->contentOnly()
            ->setValidatorClass(BecomeVendorRequest::class)
            ->formClass('become-vendor-form')
            ->setUrl(route('marketplace.vendor.become-vendor.post'))
            ->add(
                'is_vendor',
                'hidden',
                TextFieldOption::make()->value(1),
            )
            ->add(
                'shop_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Shop Name'))
                    ->placeholder(__('Store Name'))
                    ->required(),
            )
            ->add(
                'shop_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Shop URL'))
                    ->placeholder(__('Store URL'))
                    ->attributes([
                        'data-url' => route('public.ajax.check-store-url'),
                        'style' => 'direction: ltr; text-align: left;',
                    ])
                    ->wrapperAttributes(['class' => 'shop-url-wrapper mb-3 position-relative'])
                    ->prepend(
                        sprintf(
                            '<span class="position-absolute top-0 end-0 shop-url-status"></span><div class="input-group"><span class="input-group-text">%s</span>',
                            route('public.store', ['slug' => '/'])
                        )
                    )
                    ->append('</div>')
                    ->helperText(__('plugins/marketplace::store.forms.shop_url_helper'))
                    ->required(),
            )
            ->add(
                'shop_phone',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Shop Phone'))
                    ->placeholder(__('Ex: 0943243332'))
                    ->required(),
            )
            ->when(MarketplaceHelper::getSetting('requires_vendor_documentations_verification', true), function (): void {
                $customer = auth('customer')->user();
                $store = $customer?->store;

                $this
                    ->add(
                        'pan_card',
                        'html',
                        HtmlFieldOption::make()
                            ->label(__('PAN Card'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'pan_card_file'])
                            ->content(
                                ($store?->pan_card_file ? '<div class="mb-2"><a href="' . route('marketplace.vendor.become-vendor.download', ['file' => 'pan_card']) . '" target="_blank" class="btn btn-sm btn-info">View Uploaded PAN Card</a></div>' : '') .
                                '<div id="pan-card-dropzone" class="dropzone" data-placeholder="' . __('Drop PAN Card here or click to upload') . '"></div>'
                            ),
                    )
                    ->add(
                        'aadhar_card',
                        'html',
                        HtmlFieldOption::make()
                            ->label(__('Aadhar Card'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'aadhar_card_file'])
                            ->content(
                                ($store?->aadhar_card_file ? '<div class="mb-2"><a href="' . route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_card']) . '" target="_blank" class="btn btn-sm btn-info">View Uploaded Aadhar Card</a></div>' : '') .
                                '<div id="aadhar-card-dropzone" class="dropzone" data-placeholder="' . __('Drop Aadhar Card here or click to upload') . '"></div>'
                            ),
                    )
                    ->add(
                        'gst_certificate',
                        'html',
                        HtmlFieldOption::make()
                            ->label(__('GST Certificate'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'gst_certificate_file'])
                            ->content(
                                ($store?->gst_certificate_file ? '<div class="mb-2"><a href="' . route('marketplace.vendor.become-vendor.download', ['file' => 'gst_certificate']) . '" target="_blank" class="btn btn-sm btn-info">View Uploaded GST Certificate</a></div>' : '') .
                                '<div id="gst-certificate-dropzone" class="dropzone" data-placeholder="' . __('Drop GST Certificate here or click to upload') . '"></div>'
                            ),
                    )
                    ->add(
                        'udyam_aadhar',
                        'html',
                        HtmlFieldOption::make()
                            ->label(__('Udyam Aadhar'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'udyam_aadhar_file'])
                            ->content(
                                ($store?->udyam_aadhar_file ? '<div class="mb-2"><a href="' . route('marketplace.vendor.become-vendor.download', ['file' => 'udyam_aadhar']) . '" target="_blank" class="btn btn-sm btn-info">View Uploaded Udyam Aadhar</a></div>' : '') .
                                '<div id="udyam-aadhar-dropzone" class="dropzone" data-placeholder="' . __('Drop Udyam Aadhar here or click to upload') . '"></div>'
                            ),
                    );
            })
            ->add(
                'agree_terms_and_policy',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->when(
                        $privacyPolicyUrl = MarketplaceHelper::getSetting('term_and_privacy_policy_url') ?: Theme::termAndPrivacyPolicyUrl(),
                        function (CheckboxFieldOption $fieldOption, string $url): void {
                            $fieldOption->label(__('I agree to the :link', ['link' => Html::link($url, __('Terms and Privacy Policy'), attributes: ['class' => 'text-decoration-underline', 'target' => '_blank'])]));
                        }
                    )
                    ->when(! $privacyPolicyUrl, function (CheckboxFieldOption $fieldOption): void {
                        $fieldOption->label(__('I agree to the Terms and Privacy Policy'));
                    })
            )
            ->add(
                'submit',
                'submit',
                ButtonFieldOption::make()
                    ->label(__('Register'))
                    ->cssClass('btn btn-primary')
            );
    }
}
