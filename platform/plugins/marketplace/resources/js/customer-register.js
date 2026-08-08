// Disable Dropzone auto-discovery
if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false
}

window.addEventListener('load', function() {
    const jQ = typeof jQuery !== 'undefined' ? jQuery : (typeof $ !== 'undefined' ? $ : null)
    if (!jQ) { console.error('jQuery not found'); return }

    const registrationForm = jQ('form.js-base-form')
    if (!registrationForm.length) return

    // Direct vendor field visibility control
    function updateVendorFieldsVisibility(isVendor) {
        registrationForm.find('.vendor-field').each(function() {
            if (isVendor) {
                jQ(this).css({
                    'display': '',
                    'visibility': 'visible',
                    'height': 'auto',
                    'overflow': 'visible'
                }).removeClass('d-none')
                registrationForm.addClass('vendor-active')
            } else {
                jQ(this).css({
                    'display': 'none',
                    'visibility': 'hidden',
                    'height': '0',
                    'overflow': 'hidden',
                    'margin': '0',
                    'padding': '0',
                    'border': '0'
                }).addClass('d-none')
                registrationForm.removeClass('vendor-active')
            }
        })
    }

    // Initialize date pickers
    function initializeDatePickers() {
        registrationForm.find('input[type="text"][class*="date"]').each(function() {
            const input = jQ(this)
            // Check if flatpickr is available
            if (typeof flatpickr !== 'undefined' && !this._flatpickr) {
                try {
                    flatpickr(this, {
                        dateFormat: 'Y-m-d',
                        allowInput: true
                    })
                } catch (e) {
                    console.warn('Flatpickr initialization failed:', e)
                }
            }
            // Also try jQuery datepicker if available
            if (typeof jQ.datepicker !== 'undefined') {
                try {
                    input.datepicker({
                        dateFormat: 'yy-mm-dd'
                    })
                } catch (e) {
                    console.warn('jQuery datepicker initialization failed:', e)
                }
            }
        })
    }

    // Initialize dropzones
    function initializeDropzones() {
        if (typeof window.initVendorDropzones === 'function') {
            window.initVendorDropzones()
        }
    }

    // Handle is_vendor radio change
    registrationForm.on('change', 'input[name=is_vendor]', function() {
        const isVendor = jQ(this).val() == 1
        updateVendorFieldsVisibility(isVendor)
        if (isVendor) {
            setTimeout(() => {
                initializeDatePickers()
                initializeDropzones()
            }, 100)
        }
    })

    // Initialize on page load
    const isVendorChecked = registrationForm.find('input[name=is_vendor]:checked').val() == 1
    updateVendorFieldsVisibility(isVendorChecked)
    if (isVendorChecked) {
        setTimeout(() => {
            initializeDatePickers()
            initializeDropzones()
        }, 100)
    }

    // Shop URL availability check
    registrationForm.on('change', 'input[name="shop_url"]', function() {
        const input = jQ(this)
        const url = input.val()
        if (!url) return
        jQ.ajax({
            url: input.data('url'),
            type: 'POST',
            data: { url },
            beforeSend: () => {
                input.prop('disabled', true)
                registrationForm.find('button[type=submit]').prop('disabled', true)
            },
            success: ({ error, message, data }) => {
                if (error) {
                    input.addClass('is-invalid').removeClass('is-valid')
                    registrationForm.find('.shop-url-status').removeClass('text-success').addClass('text-danger').text(message)
                } else {
                    input.removeClass('is-invalid').addClass('is-valid')
                    registrationForm.find('.shop-url-status').removeClass('text-danger').addClass('text-success').text(message)
                    registrationForm.find('button[type=submit]').prop('disabled', false)
                }
                if (data?.slug) {
                    registrationForm.find('[data-slug-value]').html(
                        `${registrationForm.find('[data-slug-value]').data('base-url')}/<strong>${data.slug.substring(0, 100).toLowerCase()}</strong>`
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

        console.log('Submitting vendor form. aadhar_file_1 present:', formData.has('aadhar_file_1'), '| business_doc_file present:', formData.has('business_doc_file'))

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
                console.log('ERROR:', xhr.status, xhr.responseJSON)
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
                // Re-initialize form fields after error
                setTimeout(() => {
                    initializeDatePickers()
                }, 100)
            },
        })
    }

    // become-vendor page
    if (jQ('.become-vendor-form').length) {
        setTimeout(() => {
            initializeDatePickers()
            initializeDropzones()
        }, 100)
    }
})
