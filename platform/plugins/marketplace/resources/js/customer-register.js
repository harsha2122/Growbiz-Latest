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
            // Find all text inputs that might be date pickers
            const textInputs = form.querySelectorAll('input[type="text"]')
            textInputs.forEach(input => {
                const parent = input.closest('.vendor-field')
                if (parent && parent.style.display !== 'none' && !input._flatpickr) {
                    // Check if it looks like a date input by checking nearby labels
                    const label = input.closest('.form-group')?.querySelector('label')
                    if (label && label.textContent.toLowerCase().includes('date')) {
                        if (typeof flatpickr !== 'undefined') {
                            try {
                                flatpickr(input, {
                                    dateFormat: 'Y-m-d',
                                    allowInput: true,
                                    enableTime: false,
                                })
                            } catch (e) {
                                console.log('Date picker init:', e.message)
                            }
                        }
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
            const textInputs = form.querySelectorAll('input[type="text"]')
            textInputs.forEach(input => {
                const label = input.closest('.form-group')?.querySelector('label')
                if (label && label.textContent.toLowerCase().includes('date') && !input._flatpickr) {
                    try {
                        flatpickr(input, {
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                            enableTime: false,
                        })
                    } catch (e) {
                        console.log('Date picker init on load:', e.message)
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
