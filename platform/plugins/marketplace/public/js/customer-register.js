console.log('=== Marketplace Customer Register JS Loaded ===')

// Disable Dropzone auto-discovery to prevent "No URL provided" error
if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false
}

// Wait for both DOM and all scripts to load
window.addEventListener('load', function() {
    console.log('=== Window Loaded - Checking Dependencies ===')
    console.log('jQuery available?', typeof jQuery !== 'undefined' || typeof $ !== 'undefined')
    console.log('Dropzone available?', typeof Dropzone !== 'undefined')

    // Use jQuery or $ depending on what's available
    const jQ = typeof jQuery !== 'undefined' ? jQuery : (typeof $ !== 'undefined' ? $ : null)

    if (!jQ) {
        console.error('ERROR: jQuery not found! Vendor registration will not work.')
        return
    }

    if (typeof Dropzone === 'undefined') {
        console.error('ERROR: Dropzone not found! Document uploads will not work.')
        return
    }

    console.log('=== Initializing Vendor Registration ===')

    function initializeDropzones() {
        if (typeof window.initVendorDropzones === 'function') {
            window.initVendorDropzones()
        }

        if (!window.certificateDropzone && jQ('#certificate-dropzone').length) {
            console.log('Initializing certificate dropzone')
            window.certificateDropzone = new Dropzone('#certificate-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'certificate_file',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#certificate-dropzone').data('placeholder'),
                maxfilesexceeded: function(file) {
                    this.removeFile(file)
                }
            })
        }

        if (!window.governmentIdDropzone && jQ('#government-id-dropzone').length) {
            console.log('Initializing government ID dropzone')
            window.governmentIdDropzone = new Dropzone('#government-id-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'government_id_file',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#government-id-dropzone').data('placeholder'),
                maxfilesexceeded: function(file) {
                    this.removeFile(file)
                }
            })
        }
    }

    // Check initial state on page load
    const checkedVendorRadio = jQ('input[name=is_vendor]:checked')
    if (checkedVendorRadio.length && checkedVendorRadio.val() == 1) {
        console.log('Page loaded with vendor selected, showing form')
        jQ('[data-bb-toggle="vendor-info"]').show()
        initializeDropzones()
    }

    // Handle radio button changes
    jQ(document).on('change', 'input[name=is_vendor]', (e) => {
        const currentTarget = jQ(e.currentTarget)
        console.log('Vendor radio changed, value:', currentTarget.val())

        if (currentTarget.val() == 1) {
            console.log('Showing vendor form')
            jQ('[data-bb-toggle="vendor-info"]').slideDown(() => {
                setTimeout(() => {
                    initializeDropzones()
                }, 100)
            })
        } else {
            console.log('Hiding vendor form')
            jQ('[data-bb-toggle="vendor-info"]').slideUp()
            currentTarget.closest('form').find('button[type=submit]').prop('disabled', false)
        }
    })

    jQ('form.js-base-form input[name="shop_url"]').on('change', (e) => {
        const currentTarget = jQ(e.currentTarget)
        const form = currentTarget.closest('form')
        const url = currentTarget.val()

        if (!url) {
            return
        }

        const slug = form.find('[data-slug-value]')

        jQ.ajax({
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
                    jQ('.shop-url-status').removeClass('text-success').addClass('text-danger').text(message)
                } else {
                    currentTarget.removeClass('is-invalid').addClass('is-valid')
                    jQ('.shop-url-status').removeClass('text-danger').addClass('text-success').text(message)
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
        const form = jQ(e.target)

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
        formData.delete('certificate_file_input')
        formData.delete('government_id_file_input')

        if (window.certificateDropzone && window.certificateDropzone.files.length > 0) {
            const file = window.certificateDropzone.files[0]
            formData.append('certificate_file', file, file.name)
            console.log('Certificate file added:', file.name, 'Type:', file.type, 'Size:', file.size)
        } else {
            console.log('ERROR: No certificate file uploaded')
        }

        if (window.governmentIdDropzone && window.governmentIdDropzone.files.length > 0) {
            const file = window.governmentIdDropzone.files[0]
            formData.append('government_id_file', file, file.name)
            console.log('Government ID file added:', file.name, 'Type:', file.type, 'Size:', file.size)
        } else {
            console.log('ERROR: No government ID file uploaded')
        }

        console.log('Submitting to:', form.prop('action'))
        console.log('FormData entries:')
        for (let pair of formData.entries()) {
            console.log(pair[0], ':', pair[1])
        }

        jQ.ajax({
            url: form.prop('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                console.log('SUCCESS Response:', response)
                if (response?.data?.next_page) {
                    window.location.href = response.data.next_page
                } else if (response?.data?.next_url) {
                    window.location.href = response.data.next_url
                } else if (response?.data?.redirect_url) {
                    window.location.href = response.data.redirect_url
                } else if (response?.redirect_url) {
                    window.location.href = response.redirect_url
                } else if (response?.data?.url) {
                    window.location.href = response.data.url
                } else {
                    window.location.href = '/'
                }
            },
            error: function(xhr) {
                console.log('ERROR Response Status:', xhr.status)
                console.log('ERROR Response JSON:', xhr.responseJSON)
                console.log('ERROR Response Text:', xhr.responseText)

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

    if (jQ('.become-vendor-form').length) {
        initializeDropzones()
    }

    console.log('=== Vendor Registration Initialization Complete ===')
})
