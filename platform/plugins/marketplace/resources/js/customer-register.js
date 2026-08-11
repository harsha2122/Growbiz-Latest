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
        toggleVendorFields(checkedRadio.value === '1')
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
