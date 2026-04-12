console.log('=== Marketplace Customer Register JS Loaded ===')

// Disable Dropzone auto-discovery to prevent "No URL provided" error
if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false
}

// File cache updated via Dropzone events — much more reliable than reading dz.files at submit time
window._dzFiles = window._dzFiles || {}

// Wait for both DOM and all scripts to load
window.addEventListener('load', function() {
    console.log('=== Window Loaded - Checking Dependencies ===')

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

    /**
     * Bind addedfile/removedfile events to a Dropzone instance so we track the
     * current file in window._dzFiles[key]. Safe to call multiple times (no-op
     * if already bound via the _filesTracked flag).
     */
    function trackDropzoneFiles(dz, key) {
        if (!dz || dz._filesTracked) return
        dz._filesTracked = true
        dz.on('addedfile', function(file) {
            window._dzFiles[key] = file
            console.log('File cached for', key, ':', file.name)
        })
        dz.on('removedfile', function() {
            window._dzFiles[key] = null
            console.log('File removed from cache for', key)
        })
    }

    /**
     * Get or create a Dropzone for elementId, then track files via events.
     * Returns the Dropzone instance.
     */
    function setupDropzone(elementId, globalVar, fileKey, options) {
        const el = document.getElementById(elementId)
        if (!el) return null

        // Re-use an existing instance (created by inline script or previous call)
        let dz = el.dropzone || window[globalVar]

        if (!dz) {
            // Create a fresh instance
            const defaults = {
                url: '#',
                autoProcessQueue: false,
                maxFiles: 1,
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#' + elementId).data('placeholder') || 'Drop file here',
                maxfilesexceeded: function(file) { this.removeFile(file) },
            }
            dz = new Dropzone('#' + elementId, Object.assign(defaults, options))
            window[globalVar] = dz
        }

        trackDropzoneFiles(dz, fileKey)
        return dz
    }

    function initializeDropzones() {
        setupDropzone('aadhar-file-1-dropzone', 'aadharFile1Dropzone', 'aadhar1', {
            paramName: 'aadhar_file_1',
            acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
            dictDefaultMessage: 'Drop Aadhaar PDF or Front Image here',
        })

        setupDropzone('aadhar-file-2-dropzone', 'aadharFile2Dropzone', 'aadhar2', {
            paramName: 'aadhar_file_2',
            acceptedFiles: '.jpg,.jpeg,.png,.webp',
            dictDefaultMessage: 'Drop Aadhaar Back Image here',
        })

        setupDropzone('business-doc-dropzone', 'businessDocDropzone', 'businessDoc', {
            paramName: 'business_doc_file',
            acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
            dictDefaultMessage: 'Drop Business Document here or click to upload',
        })
    }

    // Aadhaar mode toggle
    jQ(document).on('change', 'input[name="aadhar_mode"]', function() {
        const mode = jQ(this).val()
        const wrapper = jQ('#aadhar-file-2-wrapper')
        if (mode === 'images') {
            wrapper.show()
            initializeDropzones()
        } else {
            wrapper.hide()
            if (window.aadharFile2Dropzone) {
                window.aadharFile2Dropzone.removeAllFiles(true)
            }
            window._dzFiles['aadhar2'] = null
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
                setTimeout(() => initializeDropzones(), 100)
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

        console.log('CAPTURE PHASE - Form submit detected, isVendor:', isVendor)

        if (isVendor) {
            e.preventDefault()
            e.stopPropagation()
            e.stopImmediatePropagation()
            handleVendorRegistration(form)
            return false
        }
    }, true) // true = use capture phase

    function appendFileFromDropzone(formData, fieldName, fileKey, dzElementId, dzGlobalVar) {
        // Primary: use event-cached file (most reliable)
        const cached = window._dzFiles[fileKey]
        if (cached) {
            formData.append(fieldName, cached, cached.name)
            console.log('Appended', fieldName, 'from cache:', cached.name, 'size:', cached.size)
            return true
        }

        // Fallback: read from Dropzone instance directly
        const el = document.getElementById(dzElementId)
        const dz = (el && el.dropzone) || window[dzGlobalVar] ||
            (el && Dropzone.instances && Dropzone.instances.find(d => d.element === el)) ||
            null

        if (dz && dz.files && dz.files.length > 0) {
            const file = dz.files[0]
            formData.append(fieldName, file, file.name)
            console.log('Appended', fieldName, 'from dz.files:', file.name, 'size:', file.size)
            return true
        }

        console.error('NO FILE for', fieldName,
            '| _dzFiles[' + fileKey + ']:', cached,
            '| el:', el,
            '| el.dropzone:', el && el.dropzone,
            '| window var:', window[dzGlobalVar],
            '| dz.files:', dz && dz.files)
        return false
    }

    function handleVendorRegistration(form) {
        console.log('=== Vendor Registration Started ===')

        // Ensure dropzones are initialized and event tracking is active
        initializeDropzones()

        const formData = new FormData(form.get(0))

        // Remove stale file fields — we re-append from Dropzone
        formData.delete('aadhar_file_1')
        formData.delete('aadhar_file_2')
        formData.delete('business_doc_file')

        appendFileFromDropzone(formData, 'aadhar_file_1', 'aadhar1', 'aadhar-file-1-dropzone', 'aadharFile1Dropzone')

        // Only append aadhar_file_2 when images mode is selected
        const aadharMode = form.find('input[name="aadhar_mode"]:checked').val()
        if (aadharMode === 'images') {
            appendFileFromDropzone(formData, 'aadhar_file_2', 'aadhar2', 'aadhar-file-2-dropzone', 'aadharFile2Dropzone')
        }

        appendFileFromDropzone(formData, 'business_doc_file', 'businessDoc', 'business-doc-dropzone', 'businessDocDropzone')

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
