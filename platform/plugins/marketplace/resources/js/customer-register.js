if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false
}

function initEstablishmentDatePicker(wrapperEl) {
    if (!wrapperEl || wrapperEl.dataset.flatpickrInitialized) return

    // Always allow direct typing, regardless of whether flatpickr manages to load
    const inputEl = wrapperEl.querySelector('input[data-input]')
    if (inputEl) {
        inputEl.removeAttribute('readonly')
    }

    if (typeof jQuery === 'undefined' || !jQuery.fn.flatpickr) return

    jQuery(wrapperEl).flatpickr({
        dateFormat: 'Y-m-d',
        wrap: true,
        allowInput: true,
        maxDate: 'today',
    })

    wrapperEl.dataset.flatpickrInitialized = 'true'
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.js-base-form')
        || document.querySelector('form[name="register_form"]')
        || document.querySelector('form.become-vendor-form')
    if (!form) return

    const radioInputs = form.querySelectorAll('input[name="is_vendor"]')

    function toggleVendorFields(isVendor) {
        const fields = form.querySelectorAll('.vendor-field')
        fields.forEach(el => {
            el.style.display = isVendor ? '' : 'none'
        })

        if (isVendor) {
            const wrapperEl = form.querySelector('.vendor-field .datepicker')
            initEstablishmentDatePicker(wrapperEl)
        }
    }

    if (radioInputs.length) {
        // Register page: establishment date is inside a vendor-only toggled section
        radioInputs.forEach(radio => {
            radio.addEventListener('change', function() {
                toggleVendorFields(this.value === '1')
            })
        })

        const checkedRadio = form.querySelector('input[name="is_vendor"]:checked')
        if (checkedRadio) {
            toggleVendorFields(checkedRadio.value === '1')
        }

        // Retry once after full page load, in case flatpickr/jQuery weren't ready yet
        window.addEventListener('load', function() {
            if (form.querySelector('input[name="is_vendor"]:checked')?.value === '1') {
                const wrapperEl = form.querySelector('.vendor-field .datepicker')
                initEstablishmentDatePicker(wrapperEl)
            }
        })
    } else {
        // Become-vendor page: establishment date is always visible, not gated by a toggle
        const wrapperEl = form.querySelector('.datepicker')
        initEstablishmentDatePicker(wrapperEl)

        window.addEventListener('load', function() {
            initEstablishmentDatePicker(form.querySelector('.datepicker'))
        })
    }
})

window.addEventListener('load', function() {
    const form = document.querySelector('form.js-base-form')
        || document.querySelector('form[name="register_form"]')
        || document.querySelector('form.become-vendor-form')
    if (!form) return

    // Aadhar mode toggle
    const aadharRadios = form.querySelectorAll('input[name="aadhar_mode"]')
    if (aadharRadios.length) {
        aadharRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const wrapper2 = document.getElementById('aadhar-file-2-wrapper')
                if (wrapper2) {
                    wrapper2.style.display = this.value === 'images' ? 'block' : 'none'
                }
            })
        })
    }
})
