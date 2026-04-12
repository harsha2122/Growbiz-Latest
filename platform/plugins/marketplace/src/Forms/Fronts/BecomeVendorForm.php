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

                $aadharMode = $store?->aadhar_file_2 ? 'images' : 'pdf';

                $aadharContent = '
<div class="mb-2">
    <div class="btn-group btn-group-sm" role="group">
        <input type="radio" class="btn-check" name="aadhar_upload_mode" id="aadhar-mode-pdf" value="pdf" ' . ($aadharMode === 'pdf' ? 'checked' : '') . '>
        <label class="btn btn-outline-secondary" for="aadhar-mode-pdf">Upload as PDF</label>
        <input type="radio" class="btn-check" name="aadhar_upload_mode" id="aadhar-mode-images" value="images" ' . ($aadharMode === 'images' ? 'checked' : '') . '>
        <label class="btn btn-outline-secondary" for="aadhar-mode-images">Upload as Images (Front &amp; Back)</label>
    </div>
</div>
' . ($store?->aadhar_file_1 ? '<div class="mb-2"><a href="' . route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_1']) . '" target="_blank" class="btn btn-sm btn-info">View Uploaded Aadhaar (Side 1)</a></div>' : '') . '
' . ($store?->aadhar_file_2 ? '<div class="mb-2"><a href="' . route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_2']) . '" target="_blank" class="btn btn-sm btn-info">View Uploaded Aadhaar (Side 2)</a></div>' : '') . '
<div id="aadhar-dropzone-1" class="dropzone mb-2" data-placeholder="Drop Aadhaar PDF here or click to upload"></div>
<div id="aadhar-dropzone-2" class="dropzone" data-placeholder="Drop Aadhaar Back Image here or click to upload" style="display:none;"></div>';

                $businessDocContent = '
<div class="mb-2">
    <div class="d-flex gap-3 flex-wrap">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="business_doc_type" id="bdt-gst" value="gst_certificate" ' . ($store?->business_doc_type === 'gst_certificate' || !$store?->business_doc_type ? 'checked' : '') . '>
            <label class="form-check-label" for="bdt-gst">GST Certificate</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="business_doc_type" id="bdt-shop" value="shop_act" ' . ($store?->business_doc_type === 'shop_act' ? 'checked' : '') . '>
            <label class="form-check-label" for="bdt-shop">Shop Act</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="business_doc_type" id="bdt-udyam" value="udyam_aadhar" ' . ($store?->business_doc_type === 'udyam_aadhar' ? 'checked' : '') . '>
            <label class="form-check-label" for="bdt-udyam">Udyam Aadhaar</label>
        </div>
    </div>
</div>
' . ($store?->business_doc_file ? '<div class="mb-2"><a href="' . route('marketplace.vendor.become-vendor.download', ['file' => 'business_doc']) . '" target="_blank" class="btn btn-sm btn-info">View Uploaded Business Document</a></div>' : '') . '
<div id="business-doc-dropzone" class="dropzone" data-placeholder="Drop Business Document here or click to upload"></div>';

                $this
                    ->add(
                        'aadhar_doc',
                        'html',
                        HtmlFieldOption::make()
                            ->label(__('Aadhaar Card'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'aadhar_file_1'])
                            ->content($aadharContent),
                    )
                    ->add(
                        'business_doc',
                        'html',
                        HtmlFieldOption::make()
                            ->label(__('Business Document'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'business_doc_file'])
                            ->content($businessDocContent),
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
