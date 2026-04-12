// Disable Dropzone auto-discovery to prevent "No URL provided" error
if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false
}

window.addEventListener('load', function() {
    const jQ = typeof jQuery !== 'undefined' ? jQuery : (typeof $ !== 'undefined' ? $ : null)

    if (!jQ || typeof Dropzone === 'undefined') {
        return
    }

    function initializeDropzones() {
        if (typeof window.initVendorDropzones === 'function') {
            window.initVendorDropzones()
        }

        if (!window.aadharDropzone1 && jQ('#aadhar-dropzone-1').length) {
            window.aadharDropzone1 = new Dropzone('#aadhar-dropzone-1', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'aadhar_file_1',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#aadhar-dropzone-1').data('placeholder') || 'Drop Aadhaar here or click to upload',
                maxfilesexceeded: function(file) { this.removeFile(file) }
            })
        }

        if (!window.aadharDropzone2 && jQ('#aadhar-dropzone-2').length) {
            window.aadharDropzone2 = new Dropzone('#aadhar-dropzone-2', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'aadhar_file_2',
                maxFiles: 1,
                acceptedFiles: '.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#aadhar-dropzone-2').data('placeholder') || 'Drop Aadhaar Back here or click to upload',
                maxfilesexceeded: function(file) { this.removeFile(file) }
            })
        }

        if (!window.businessDocDropzone && jQ('#business-doc-dropzone').length) {
            window.businessDocDropzone = new Dropzone('#business-doc-dropzone', {
                url: '#',
                autoProcessQueue: false,
                paramName: 'business_doc_file',
                maxFiles: 1,
                acceptedFiles: '.pdf,.jpg,.jpeg,.png,.webp',
                addRemoveLinks: true,
                dictDefaultMessage: jQ('#business-doc-dropzone').data('placeholder') || 'Drop Business Document here or click to upload',
                maxfilesexceeded: function(file) { this.removeFile(file) }
            })
        }

        // Aadhaar mode toggle
        jQ(document).on('change', 'input[name="aadhar_upload_mode"]', function() {
            const dz2 = jQ('#aadhar-dropzone-2')
            if (jQ(this).val() === 'images') {
                dz2.show()
            } else {
                dz2.hide()
                if (window.aadharDropzone2) window.aadharDropzone2.removeAllFiles()
            }
        })
    }

    // Check initial state on page load
    const checkedVendorRadio = jQ('input[name=is_vendor]:checked')
    if (checkedVendorRadio.length && checkedVendorRadio.val() == 1) {
        jQ('[data-bb-toggle="vendor-info"]').show()
        initializeDropzones()
    }

    // Handle radio button changes
    jQ(document).on('change', 'input[name=is_vendor]', (e) => {
        const currentTarget = jQ(e.currentTarget)
        if (currentTarget.val() == 1) {
            jQ('[data-bb-toggle="vendor-info"]').slideDown(() => {
                setTimeout(() => { initializeDropzones() }, 100)
            })
        } else {
            jQ('[data-bb-toggle="vendor-info"]').slideUp()
            currentTarget.closest('form').find('button[type=submit]').prop('disabled', false)
        }
    })

    jQ('form.js-base-form input[name="shop_url"]').on('change', (e) => {
        const currentTarget = jQ(e.currentTarget)
        const form = currentTarget.closest('form')
        const url = currentTarget.val()

        if (!url) return

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
                    slug.html(`${slug.data('base-url')}/<strong>${data.slug.substring(0, 100).toLowerCase()}</strong>`)
                }
            },
            complete: () => currentTarget.prop('disabled', false),
        })
    })

    document.addEventListener('submit', function(e) {
        const form = jQ(e.target)

        if (!form.hasClass('become-vendor-form') && !form.hasClass('js-base-form')) {
            return
        }

        const isVendorInput = form.find('input[name="is_vendor"]:checked').val()
        const isVendor = isVendorInput == 1 || form.hasClass('become-vendor-form')

        if (isVendor) {
            e.preventDefault()
            e.stopPropagation()
            e.stopImmediatePropagation()
            handleVendorRegistration(form)
            return false
        }
    }, true)

    function handleVendorRegistration(form) {
        initializeDropzones()

        const formData = new FormData(form.get(0))

        formData.delete('aadhar_file_1')
        formData.delete('aadhar_file_2')
        formData.delete('business_doc_file')

        if (window.aadharDropzone1 && window.aadharDropzone1.files.length > 0) {
            const file = window.aadharDropzone1.files[0]
            formData.append('aadhar_file_1', file, file.name)
        }

        const aadharMode = form.find('input[name="aadhar_upload_mode"]:checked').val()
        if (aadharMode === 'images' && window.aadharDropzone2 && window.aadharDropzone2.files.length > 0) {
            const file = window.aadharDropzone2.files[0]
            formData.append('aadhar_file_2', file, file.name)
        }

        if (window.businessDocDropzone && window.businessDocDropzone.files.length > 0) {
            const file = window.businessDocDropzone.files[0]
            formData.append('business_doc_file', file, file.name)
        }

        jQ.ajax({
            url: form.prop('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
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
                const errors = xhr.responseJSON?.errors || {}

                form.find('input').removeClass('is-invalid')
                form.find('.invalid-feedback').remove()

                Object.keys(errors).forEach((key) => {
                    const error = Array.isArray(errors[key]) ? errors[key][0] : errors[key]

                    if (['aadhar_file_1', 'aadhar_file_2', 'business_doc_file', 'business_doc_type'].includes(key)) {
                        const fieldMap = {
                            'aadhar_file_1': 'aadhar_file_1',
                            'aadhar_file_2': 'aadhar_file_1',
                            'business_doc_file': 'business_doc_file',
                            'business_doc_type': 'business_doc_file',
                        }
                        const wrapper = form.find(`[data-field-name="${fieldMap[key]}"]`)
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
})
