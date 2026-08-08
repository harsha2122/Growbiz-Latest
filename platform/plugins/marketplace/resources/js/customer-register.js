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
            if (isVendor) {
                el.style.display = ''
                el.removeAttribute('style')
            } else {
                el.style.display = 'none'
            }
        })
    }

    function initDatePickers() {
        setTimeout(() => {
            const dateInputs = form.querySelectorAll('input[type="text"].form-control')
            dateInputs.forEach(input => {
                const wrapper = input.closest('.vendor-field')
                if (wrapper && wrapper.style.display !== 'none') {
                    if (typeof flatpickr !== 'undefined' && !input._flatpickr) {
                        try {
                            flatpickr(input, { dateFormat: 'Y-m-d', allowInput: true })
                        } catch (e) {
                            console.log('Flatpickr:', e.message)
                        }
                    }
                }
            })
        }, 50)
    }

    radioInputs.forEach(radio => {
        radio.addEventListener('change', function() {
            const isVendor = this.value === '1'
            toggleVendorFields(isVendor)
            if (isVendor) {
                initDatePickers()
                if (typeof window.initVendorDropzones === 'function') {
                    setTimeout(() => window.initVendorDropzones(), 100)
                }
            }
        })
    })

    // Initial state
    const checkedRadio = form.querySelector('input[name="is_vendor"]:checked')
    if (checkedRadio) {
        const isVendor = checkedRadio.value === '1'
        toggleVendorFields(isVendor)
        if (isVendor) {
            initDatePickers()
        }
    }
})

window.addEventListener('load', function() {
    const form = document.querySelector('form.js-base-form') || document.querySelector('form[name="register_form"]')
    if (!form) return

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
