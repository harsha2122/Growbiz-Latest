if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.js-base-form') || document.querySelector('form[name="register_form"]')
    if (!form) return

    const radioInputs = form.querySelectorAll('input[name="is_vendor"]')
    if (!radioInputs.length) return

    function toggleVendorFields(isVendor) {
        const fields = form.querySelectorAll('.vendor-field')
        fields.forEach(el => {
            el.style.display = isVendor ? '' : 'none'
        })

        if (isVendor) {
            initDatePickers()
        }
    }

    function initDatePickers() {
        setTimeout(() => {
            if (typeof flatpickr === 'undefined') {
                console.log('Flatpickr not loaded')
                return
            }

            // Find all date picker inputs in vendor fields that are visible
            const dateInputs = form.querySelectorAll('.datepicker input[data-input]')
            dateInputs.forEach(input => {
                const wrapper = input.closest('.datepicker')
                const vendorField = input.closest('.vendor-field')

                // Only init if visible and not already initialized
                if (wrapper && (!vendorField || vendorField.offsetParent !== null) && !input._flatpickr) {
                    try {
                        flatpickr(input, {
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                            enableTime: false,
                        })
                        console.log('Date picker initialized:', input.name)
                    } catch (e) {
                        console.error('Date picker init error:', e.message)
                    }
                }
            })
        }, 100)
    }

    // Handle is_vendor change
    radioInputs.forEach(radio => {
        radio.addEventListener('change', function() {
            const isVendor = this.value === '1'
            toggleVendorFields(isVendor)
        })
    })

    // Initial state
    const checkedRadio = form.querySelector('input[name="is_vendor"]:checked')
    if (checkedRadio) {
        const isVendor = checkedRadio.value === '1'
        toggleVendorFields(isVendor)
    }
})

window.addEventListener('load', function() {
    const form = document.querySelector('form.js-base-form') || document.querySelector('form[name="register_form"]')
    if (!form) return

    // Re-initialize date pickers after page load
    setTimeout(() => {
        const isVendor = form.querySelector('input[name="is_vendor"]:checked')?.value === '1'
        if (isVendor && typeof flatpickr !== 'undefined') {
            const dateInputs = form.querySelectorAll('.datepicker input[data-input]')
            dateInputs.forEach(input => {
                if (!input._flatpickr) {
                    try {
                        flatpickr(input, {
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                            enableTime: false,
                        })
                    } catch (e) {
                        console.error('Date picker init on load:', e.message)
                    }
                }
            })
        }
    }, 500)

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
