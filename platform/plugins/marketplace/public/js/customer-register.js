// Disable Dropzone auto-discovery
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

window.addEventListener('load', function() {
    const jQ = typeof jQuery !== 'undefined' ? jQuery : (typeof $ !== 'undefined' ? $ : null)
    if (!jQ) { console.error('jQuery not found'); return }

    const form = document.querySelector('form.js-base-form')
        || document.querySelector('form[name="register_form"]')
        || document.querySelector('form.become-vendor-form')

    // Show/hide vendor form + init dropzones
    function showVendorForm() {
        jQ('[data-bb-toggle="vendor-info"]').slideDown(function() {
            setTimeout(initDropzones, 100)
        })
        if (form) {
            const wrapperEl = form.querySelector('.vendor-field .datepicker')
            initEstablishmentDatePicker(wrapperEl)
        }
    }

    function hideVendorForm() {
        jQ('[data-bb-toggle="vendor-info"]').slideUp()
    }

    function initDropzones() {
        if (typeof window.initVendorDropzones === 'function') {
            window.initVendorDropzones()
        }
    }

    // Radio: is_vendor
    jQ(document).on('change', 'input[name=is_vendor]', function() {
        jQ(this).val() == 1 ? showVendorForm() : hideVendorForm()
    })

    // If vendor pre-selected (e.g. after failed submit)
    if (jQ('input[name=is_vendor]:checked').val() == 1) {
        jQ('[data-bb-toggle="vendor-info"]').show()
        initDropzones()
        if (form) {
            const wrapperEl = form.querySelector('.vendor-field .datepicker')
            initEstablishmentDatePicker(wrapperEl)
        }
    }

    // Become-vendor page: establishment date is always visible, not gated by a toggle
    if (form && !form.querySelectorAll('input[name="is_vendor"]').length) {
        initEstablishmentDatePicker(form.querySelector('.datepicker'))
    }

    // Shop URL availability check
    jQ(document).on('change', 'form.js-base-form input[name="shop_url"]', function() {
        const input = jQ(this)
        const form = input.closest('form')
        const url = input.val()
        if (!url) return
        jQ.ajax({
            url: input.data('url'),
            type: 'POST',
            data: { url },
            beforeSend: () => { input.prop('disabled', true); form.find('button[type=submit]').prop('disabled', true) },
            success: ({ error, message, data }) => {
                if (error) {
                    input.addClass('is-invalid').removeClass('is-valid')
                    jQ('.shop-url-status').removeClass('text-success').addClass('text-danger').text(message)
                } else {
                    input.removeClass('is-invalid').addClass('is-valid')
                    jQ('.shop-url-status').removeClass('text-danger').addClass('text-success').text(message)
                    form.find('button[type=submit]').prop('disabled', false)
                }
                if (data?.slug) {
                    form.find('[data-slug-value]').html(
                        `${form.find('[data-slug-value]').data('base-url')}/<strong>${data.slug.substring(0, 100).toLowerCase()}</strong>`
                    )
                }
            },
            complete: () => input.prop('disabled', false),
        })
    })

    // Intercept form submit for vendor registration
    document.addEventListener('submit', function(e) {
        const form = jQ(e.target)
        if (!form.hasClass('become-vendor-form') && !form.hasClass('js-base-form')) return

        const isVendorVal = form.find('input[name="is_vendor"]:checked').val()
        const isVendor = isVendorVal == 1 || form.hasClass('become-vendor-form')
        if (!isVendor) return

        e.preventDefault()
        e.stopPropagation()
        e.stopImmediatePropagation()

        handleVendorRegistration(form)
    }, true)

    function handleVendorRegistration(form) {
        // FormData captures the hidden file inputs populated by DataTransfer in inline script
        const formData = new FormData(form.get(0))

        // Remove aadhar_file_2 if not in images mode
        const aadharMode = form.find('input[name="aadhar_mode"]:checked').val()
        if (aadharMode !== 'images') {
            formData.delete('aadhar_file_2')
        }

        jQ.ajax({
            url: form.prop('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                const url = response?.data?.next_page || response?.data?.next_url ||
                    response?.data?.redirect_url || response?.redirect_url ||
                    response?.data?.url || '/'
                window.location.href = url
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {}
                form.find('input').removeClass('is-invalid')
                form.find('.invalid-feedback').remove()
                Object.keys(errors).forEach(function(key) {
                    const msg = Array.isArray(errors[key]) ? errors[key][0] : errors[key]
                    if (['aadhar_file_1', 'aadhar_file_2', 'business_doc_file', 'business_doc_type'].includes(key)) {
                        const wrapper = form.find('[data-field-name="' + key + '"]')
                        wrapper.append('<div class="invalid-feedback" style="display:block">' + msg + '</div>')
                    } else {
                        const input = form.find('input[name="' + key + '"]')
                        input.addClass('is-invalid')
                        if (!input.is(':checkbox')) input.parent().append('<div class="invalid-feedback">' + msg + '</div>')
                    }
                })
            },
        })
    }

    // Aadhar mode toggle
    const aadharRadios = form ? form.querySelectorAll('input[name="aadhar_mode"]') : []
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

    // become-vendor page
    if (jQ('.become-vendor-form').length) {
        initDropzones()
    }
})
