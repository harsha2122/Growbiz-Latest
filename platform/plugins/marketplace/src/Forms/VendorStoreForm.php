<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FieldOptions\EmailFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Marketplace\Forms\Fields\CustomEditorField;
use Botble\Marketplace\Http\Requests\Fronts\VendorStoreRequest;
use Botble\Theme\Facades\Theme;

class VendorStoreForm extends StoreForm
{
    public function setup(): void
    {
        parent::setup();

        // The vendor dashboard layout doesn't load the admin JS bundle that normally
        // initializes flatpickr on `.datepicker` fields, so it has to be wired up here
        // (self-hosted, same files the admin panel uses - no CDN/network dependency).
        Theme::asset()
            ->usePath(false)
            ->add('flatpickr-css', '/vendor/core/core/base/libraries/flatpickr/flatpickr.min.css');

        Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add('flatpickr-js', '/vendor/core/core/base/libraries/flatpickr/flatpickr.min.js', ['jquery']);

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
            ->addAfter('establishment_date', 'establishment_date_datepicker_init', HtmlField::class, HtmlFieldOption::make()->content('<script>
(function () {
    function initEstablishmentDatePicker() {
        var wrapperEl = document.querySelector(".datepicker");
        if (!wrapperEl || wrapperEl.dataset.flatpickrInitialized) {
            return;
        }

        var inputEl = wrapperEl.querySelector("input[data-input]");
        if (inputEl) {
            inputEl.removeAttribute("readonly");
        }

        if (typeof jQuery === "undefined" || !jQuery.fn.flatpickr) {
            return;
        }

        jQuery(wrapperEl).flatpickr({
            dateFormat: "Y-m-d",
            wrap: true,
            allowInput: true,
            maxDate: "today",
        });

        wrapperEl.dataset.flatpickrInitialized = "true";
    }

    document.addEventListener("DOMContentLoaded", initEstablishmentDatePicker);
    window.addEventListener("load", initEstablishmentDatePicker);
})();
</script>'));
    }
}
