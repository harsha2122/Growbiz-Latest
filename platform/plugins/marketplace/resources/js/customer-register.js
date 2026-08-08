if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false
}

document.addEventListener('DOMContentLoaded', function() {
    const radioInputs = document.querySelectorAll('input[name="is_vendor"]')
    const form = document.querySelector('form.js-base-form') || document.querySelector('form')

    if (!form || !radioInputs.length) return

    // Direct vendor field toggle
    function toggleVendorFields(isVendor) {
        const fields = form.querySelectorAll('.vendor-field')
        fields.forEach(el => {
            if (isVendor) {
                el.style.display = ''
                form.classList.add('vendor-active')
            } else {
                el.style.display = 'none'
                form.classList.remove('vendor-active')
            }
        })
    }

    // Initialize date pickers after fields are visible
    function initDatePickers() {
        setTimeout(() => {
            const dateInputs = form.querySelectorAll('input[type="text"].date, input[type="date"]')
            dateInputs.forEach(input => {
                if (input.offsetParent !== null) { // Check if visible
                    if (typeof flatpickr !== 'undefined' && !input._flatpickr) {
                        try {
                            flatpickr(input, { dateFormat: 'Y-m-d' })
                        } catch (e) {
                            console.log('Flatpickr init:', e.message)
                        }
                    }
                }
            })
        }, 50)
    }

    // Radio change handler
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
    const form = document.querySelector('form.js-base-form') || document.querySelector('form')
    if (!form) return

    // Shop URL check
    const shopUrlInput = form.querySelector('input[name="shop_url"]')
    if (shopUrlInput && shopUrlInput.dataset.url) {
        shopUrlInput.addEventListener('change', function() {
            const url = this.value.trim()
            if (!url) return

            fetch(this.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url })
            })
            .then(r => r.json())
            .then(data => {
                const status = form.querySelector('.shop-url-status')
                if (status) {
                    if (data.error) {
                        this.classList.add('is-invalid')
                        status.classList.add('text-danger')
                        status.textContent = data.message
                    } else {
                        this.classList.remove('is-invalid')
                        status.classList.remove('text-danger')
                        status.textContent = data.message
                    }
                }
            })
        })
    }

    // Vendor form submit handler
    form.addEventListener('submit', function(e) {
        const isVendor = form.querySelector('input[name="is_vendor"]:checked')?.value === '1'
        if (!isVendor && !form.classList.contains('become-vendor-form')) return

        e.preventDefault()

        const formData = new FormData(form)
        const action = form.getAttribute('action')

        fetch(action, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect_url) {
                window.location.href = data.redirect_url
            } else if (data.data?.redirect_url) {
                window.location.href = data.data.redirect_url
            }
        })
        .catch(err => {
            console.error('Form submission error:', err)
        })
    })
})
