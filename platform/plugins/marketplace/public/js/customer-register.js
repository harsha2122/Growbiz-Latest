console.log('=== Marketplace Customer Register JS Loaded ===')

$(() => {
    console.log('=== Document Ready - Initializing Vendor Registration ===')

    let certificateDropzone = null
    let governmentIdDropzone = null

    function initializeDropzones() {
        if ($('#certificate-dropzone').length && !certificateDropzone) {
            console.log('Initializing certificate dropzone')
            certificateDropzone = new Dropzone('#certificate-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'certificate_file',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: $('#certificate-dropzone').data('placeholder'),
                maxfilesexceeded: function(file) {
                    this.removeFile(file)
                },
            })
        }

        if ($('#government-id-dropzone').length && !governmentIdDropzone) {
            console.log('Initializing government ID dropzone')
            governmentIdDropzone = new Dropzone('#government-id-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'government_id_file',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: $('#government-id-dropzone').data('placeholder'),
                maxfilesexceeded: function(file) {
                    this.removeFile(file)
                },
            })
        }
    }

    $(document).on('click', 'input[name=is_vendor]', (e) => {
        const currentTarget = $(e.currentTarget)

        if (currentTarget.val() == 1) {
            $('[data-bb-toggle="vendor-info"]').slideDown(() => {
                setTimeout(() => {
                    initializeDropzones()
                }, 100)
            })
        } else {
            $('[data-bb-toggle="vendor-info"]').slideUp()
            currentTarget.closest('form').find('button[type=submit]').prop('disabled', false)
        }
    })

    $('form.js-base-form input[name="shop_url"]').on('change', (e) => {
        const currentTarget = $(e.currentTarget)
        const form = currentTarget.closest('form')
        const url = currentTarget.val()

        if (!url) {
            return
        }

        const slug = form.find('[data-slug-value]')

        $.ajax({
            url: currentTarget.data('url'),
            type: 'POST',
            data: { url },
            beforeSend: () => {
                currentTarget.prop('disabled', true)
                form.find('button[type=submit]').prop('disabled', true)
            },
            success: ({ error, message, data }) => {
                if (error) {
                    currentTarget.addClass('is-invalid').removeClass('is-valid')
                    $('.shop-url-status').removeClass('text-success').addClass('text-danger').text(message)
                } else {
                    currentTarget.removeClass('is-invalid').addClass('is-valid')
                    $('.shop-url-status').removeClass('text-danger').addClass('text-success').text(message)
                    form.find('button[type=submit]').prop('disabled', false)
                }

                if (data?.slug) {
                    slug.html(
                        `${slug.data('base-url')}/<strong>${data.slug.substring(0, 100).toLowerCase()}</strong>`,
                    )
                }
            },
            complete: () => currentTarget.prop('disabled', false),
        })
    })

    // Use capture phase to intercept form submission BEFORE validation
    document.addEventListener('submit', function(e) {
        const form = $(e.target)

        if (!form.hasClass('become-vendor-form') && !form.hasClass('js-base-form')) {
            return
        }

        const isVendorInput = form.find('input[name="is_vendor"]:checked').val()
        const isVendor = isVendorInput == 1 || form.hasClass('become-vendor-form')

        console.log('CAPTURE PHASE - Form submit detected')
        console.log('Form classes:', form.attr('class'))
        console.log('isVendor:', isVendor)

        if (isVendor) {
            console.log('PREVENTING DEFAULT - Vendor registration')
            e.preventDefault()
            e.stopPropagation()
            e.stopImmediatePropagation()

            handleVendorRegistration(form)
            return false
        }
    }, true) // true = use capture phase

    function handleVendorRegistration(form) {
        console.log('=== Vendor Registration Started ===')

        initializeDropzones()

        const formData = new FormData(form.get(0))

        formData.delete('certificate_file')
        formData.delete('government_id_file')

        if (certificateDropzone && certificateDropzone.files.length > 0) {
            formData.append('certificate_file', certificateDropzone.files[0])
            console.log('Certificate file added:', certificateDropzone.files[0].name)
        } else {
            console.log('WARNING: No certificate file')
        }

        if (governmentIdDropzone && governmentIdDropzone.files.length > 0) {
            formData.append('government_id_file', governmentIdDropzone.files[0])
            console.log('Government ID file added:', governmentIdDropzone.files[0].name)
        } else {
            console.log('WARNING: No government ID file')
        }

        console.log('Submitting to:', form.prop('action'))

        $.ajax({
            url: form.prop('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('SUCCESS:', response)
                if (response?.data?.redirect_url) {
                    window.location.href = response.data.redirect_url
                } else if (response?.redirect_url) {
                    window.location.href = response.redirect_url
                }
            },
            error: function(xhr) {
                console.log('ERROR:', xhr.status, xhr.responseJSON)

                const errors = xhr.responseJSON?.errors || {}

                form.find('input').removeClass('is-invalid')
                form.find('.invalid-feedback').remove()

                Object.keys(errors).forEach((key) => {
                    const error = Array.isArray(errors[key]) ? errors[key][0] : errors[key]

                    if (['certificate_file', 'government_id_file'].includes(key)) {
                        const wrapper = form.find(`[data-field-name="${key}"]`)
                        wrapper.find('.invalid-feedback').remove()
                        wrapper.append(`<div class="invalid-feedback" style="display: block">${error}</div>`)
                    } else {
                        const input = form.find(`input[name="${key}"]`)
                        input.addClass('is-invalid')
                        if (!input.is(':checkbox')) {
                            input.parent().append(`<div class="invalid-feedback">${error}</div>`)
                        }
                    }
                })
            },
        })
    }

    if ($('.become-vendor-form').length) {
        initializeDropzones()
    }
})
