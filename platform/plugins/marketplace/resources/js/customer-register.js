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

        if (!window.aadharFile1Dropzone && jQ('#aadhar-file-1-dropzone').length) {
            console.log('Initializing Aadhaar file 1 dropzone')
            window.aadharFile1Dropzone = new Dropzone('#aadhar-file-1-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'aadhar_file_1',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#aadhar-file-1-dropzone').data('placeholder') || 'Drop Aadhaar PDF or Front Image here',
                maxfilesexceeded: function(file) {
                    this.removeFile(file)
                }
            })
        }

        if (!window.aadharFile2Dropzone && jQ('#aadhar-file-2-dropzone').length) {
            console.log('Initializing Aadhaar file 2 dropzone')
            window.aadharFile2Dropzone = new Dropzone('#aadhar-file-2-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'aadhar_file_2',
                maxFiles: 1,
                acceptedFiles: '.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#aadhar-file-2-dropzone').data('placeholder') || 'Drop Aadhaar Back Image here',
                maxfilesexceeded: function(file) {
                    this.removeFile(file)
                }
            })
        }

        if (!window.businessDocDropzone && jQ('#business-doc-dropzone').length) {
            console.log('Initializing Business Document dropzone')
            window.businessDocDropzone = new Dropzone('#business-doc-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'business_doc_file',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#business-doc-dropzone').data('placeholder') || 'Drop Business Document here or click to upload',
                maxfilesexceeded: function(file) {
                    this.removeFile(file)
                }
            })
        }
    }

    // Aadhaar mode toggle
    jQ(document).on('change', 'input[name="aadhar_mode"]', function() {
        const mode = jQ(this).val()
        const wrapper = jQ('#aadhar-file-2-wrapper')
        if (mode === 'images') {
            wrapper.show()
            // init dropzone if not yet done
            if (!window.aadharFile2Dropzone && jQ('#aadhar-file-2-dropzone').length) {
                setTimeout(initializeDropzones, 50)
            }
        } else {
            wrapper.hide()
            // clear file 2 if user switches back to PDF mode
            if (window.aadharFile2Dropzone) {
                window.aadharFile2Dropzone.removeAllFiles(true)
            }
        }
    })

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

        // Remove any stale file fields — we'll append from dropzones
        formData.delete('aadhar_file_1')
        formData.delete('aadhar_file_2')
        formData.delete('business_doc_file')

        if (window.aadharFile1Dropzone && window.aadharFile1Dropzone.files.length > 0) {
            const file = window.aadharFile1Dropzone.files[0]
            formData.append('aadhar_file_1', file, file.name)
            console.log('Aadhaar file 1 added:', file.name, 'Size:', file.size)
        } else {
            console.log('No Aadhaar file 1 uploaded')
        }

        // Only append aadhar_file_2 when images mode is selected
        const aadharMode = form.find('input[name="aadhar_mode"]:checked').val()
        if (aadharMode === 'images' && window.aadharFile2Dropzone && window.aadharFile2Dropzone.files.length > 0) {
            const file = window.aadharFile2Dropzone.files[0]
            formData.append('aadhar_file_2', file, file.name)
            console.log('Aadhaar file 2 added:', file.name, 'Size:', file.size)
        }

        if (window.businessDocDropzone && window.businessDocDropzone.files.length > 0) {
            const file = window.businessDocDropzone.files[0]
            formData.append('business_doc_file', file, file.name)
            console.log('Business doc file added:', file.name, 'Size:', file.size)
        } else {
            console.log('No Business Document file uploaded')
        }

        console.log('Submitting to:', form.prop('action'))

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

                const errors = xhr.responseJSON?.errors || {}

                form.find('input').removeClass('is-invalid')
                form.find('.invalid-feedback').remove()

                Object.keys(errors).forEach((key) => {
                    const error = Array.isArray(errors[key]) ? errors[key][0] : errors[key]

                    if (['aadhar_file_1', 'aadhar_file_2', 'business_doc_file', 'business_doc_type'].includes(key)) {
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
