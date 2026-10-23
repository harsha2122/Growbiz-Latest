@php
    $storeId = $product->store?->id;
    $gateModalId = 'whatsapp-gate-modal-' . $product->id;
@endphp

@if ($storeId)
    <div class="modal fade" id="{{ $gateModalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Book on WhatsApp') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="js-whatsapp-gate-form" method="post" action="{{ route('public.ajax.whatsapp-enquiries.store') }}" data-url="{{ route('public.ajax.whatsapp-enquiries.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="store_id" value="{{ $storeId }}">
                        <input type="hidden" name="product_id" value="{{ $product->id }}" class="js-whatsapp-gate-product-id">

                        <p class="text-muted small mb-10">{{ __('Share your name and number to continue to WhatsApp.') }}</p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Name') }}</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Contact Number') }}</label>
                            <input
                                type="tel"
                                class="form-control"
                                name="contact_number"
                                inputmode="numeric"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                minlength="10"
                                title="{{ __('Enter a valid 10-digit phone number') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                required
                            >
                        </div>
                        <div class="whatsapp-gate-form-error text-danger small d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">{{ __('Continue to WhatsApp') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Must stay inline here, not a Blade footer push - the public storefront
        // layout doesn't render Laravel's footer stack (see enquiry-button.blade.php
        // for the full explanation). A pushed script here would silently never run.
        (function () {
            var modal = document.getElementById('{{ $gateModalId }}');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            // When this modal is opened, pick up the currently selected variation's
            // product id (if the triggering button sits inside the product options
            // form and a variation has been chosen), same as the old book-now link
            // used to sync before redirecting. Falls back to the base product id
            // already baked into the hidden input otherwise.
            modal.addEventListener('show.bs.modal', function (event) {
                var trigger = event.relatedTarget;
                var productIdInput = modal.querySelector('.js-whatsapp-gate-product-id');
                var variationForm = trigger ? trigger.closest('form') : null;
                var idField = variationForm ? variationForm.querySelector('input[name="id"]') : null;

                if (productIdInput && idField && idField.value) {
                    productIdInput.value = idField.value;
                }
            });
        })();
    </script>

    @once
        <script>
            document.addEventListener('submit', function (e) {
                if (!e.target.classList.contains('js-whatsapp-gate-form')) return;
                e.preventDefault();

                var form = e.target;
                var btn = form.querySelector('button[type=submit]');
                var errorBox = form.querySelector('.whatsapp-gate-form-error');
                errorBox.classList.add('d-none');
                btn.disabled = true;

                var formData = new FormData(form);
                var data = {};
                formData.forEach(function (v, k) { data[k] = v; });

                fetch(form.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(data),
                })
                    .then(function (res) { return res.json().then(function (json) { return { ok: res.ok, json: json }; }); })
                    .then(function (result) {
                        btn.disabled = false;
                        if (result.ok && !result.json.error) {
                            var redirectUrl = result.json.data && result.json.data.redirect_url;
                            var modalEl = form.closest('.modal');
                            var modalInstance = window.bootstrap ? window.bootstrap.Modal.getInstance(modalEl) : null;
                            if (modalInstance) modalInstance.hide();

                            if (redirectUrl) {
                                window.open(redirectUrl, '_blank', 'noopener');
                            } else if (window.Botble && Botble.showSuccess) {
                                Botble.showSuccess(result.json.message || 'Thank you!');
                            }

                            form.reset();
                        } else {
                            var msg = result.json.message || 'Something went wrong.';
                            if (result.json.errors) {
                                msg = Object.values(result.json.errors).flat().join(' ');
                            }
                            errorBox.textContent = msg;
                            errorBox.classList.remove('d-none');
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        errorBox.textContent = 'Network error. Please try again.';
                        errorBox.classList.remove('d-none');
                    });
            });
        </script>
    @endonce
@endif
