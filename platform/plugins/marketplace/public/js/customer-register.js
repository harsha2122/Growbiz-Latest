$(() => {
    let certificateDropzone = null
    let governmentIdDropzone = null

    function initializeDropzones() {
        if ($('#certificate-dropzone').length && !certificateDropzone) {
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

    const vendorForms = $('form.become-vendor-form, form.js-base-form')

    vendorForms.on('submit', function(e) {
        const form = $(e.currentTarget)
        const isVendor = form.find('input[name="is_vendor"]').val() == 1 || form.hasClass('become-vendor-form')

        if (isVendor && ($('#certificate-dropzone').length || $('#government-id-dropzone').length)) {
            e.preventDefault()

            initializeDropzones()

            const formData = new FormData(form.get(0))

            // Remove default file inputs if they exist
            formData.delete('certificate_file')
            formData.delete('government_id_file')

            if (certificateDropzone && certificateDropzone.files.length > 0) {
                // Get the raw File object from Dropzone
                formData.append('certificate_file', certificateDropzone.files[0])
                console.log('Certificate file added:', certificateDropzone.files[0])
            } else {
                console.log('No certificate file found')
            }

            if (governmentIdDropzone && governmentIdDropzone.files.length > 0) {
                // Get the raw File object from Dropzone
                formData.append('government_id_file', governmentIdDropzone.files[0])
                console.log('Government ID file added:', governmentIdDropzone.files[0])
            } else {
                console.log('No government ID file found')
            }

            // Debug: Log all FormData entries
            console.log('FormData contents:')
            for (let pair of formData.entries()) {
                console.log(pair[0] + ':', pair[1])
            }

                $.ajax({
                    url: form.prop('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function({ data }) {
                        if (data?.redirect_url) {
                            window.location.href = data.redirect_url
                        }
                    },
                    error: function(response) {
                        console.log('Error response:', response)
                        console.log('Error status:', response.status)
                        console.log('Error data:', response.responseJSON)

                        const { errors } = response.responseJSON || {}

                        form.find('input').removeClass('is-invalid').removeClass('is-valid')
                        form.find('.invalid-feedback').remove()

                        if (errors) {
                            Object.keys(errors).forEach((key) => {
                                const input = form.find(`input[name="${key}"]`)
                                const error = Array.isArray(errors[key]) ? errors[key][0] : errors[key]

                                console.log('Validation error:', key, error)

                                if (['certificate_file', 'government_id_file'].includes(key)) {
                                    const wrapper = form.find(`[data-field-name="${key}"]`)
                                    wrapper.find('.invalid-feedback').remove()
                                    wrapper.append(`<div class="invalid-feedback" style="display: block">${error}</div>`)
                                } else {
                                    input.addClass('is-invalid').removeClass('is-valid')
                                    if (!input.is(':checkbox')) {
                                        input.parent().append(`<div class="invalid-feedback">${error}</div>`)
                                    }
                                }
                            })
                        }
                    },
                })
            }
        }
    })

    if ($('.become-vendor-form').length) {
        initializeDropzones()
    }
})
