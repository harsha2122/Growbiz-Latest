@php
    $storeId = $store->id ?? null;
    $productId = $product->id ?? null;
    $modalId = 'enquiry-modal-' . ($type ?? 'vendor') . '-' . ($productId ?? $storeId);
@endphp

@if ($storeId)
    <button type="button" class="btn btn-outline-primary w-100 mt-10 mb-10" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
        <x-core::icon name="ti ti-message-question" />
        {{ __('Enquire Now') }}
    </button>

    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Send an Enquiry') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="js-enquiry-form" data-url="{{ route('public.ajax.enquiries.store') }}">
                    <div class="modal-body">
                        <input type="hidden" name="store_id" value="{{ $storeId }}">
                        @if ($productId)
                            <input type="hidden" name="product_id" value="{{ $productId }}">
                        @endif
                        <input type="hidden" name="source" value="{{ $productId ? 'product_page' : 'vendor_page' }}">

                        <div class="mb-10">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-10">
                            <label class="form-label">{{ __('Contact Number') }}</label>
                            <input type="tel" class="form-control" name="contact_number" required>
                        </div>
                        <div class="mb-10">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-10">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea class="form-control" name="address" rows="2" required></textarea>
                        </div>
                        <div class="enquiry-form-error text-danger small d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">{{ __('Submit Enquiry') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modals nested inside a transformed/overflow-hidden ancestor (sliders, product
        // gallery, etc.) break Bootstrap's position:fixed and render trapped behind the
        // backdrop. Move each enquiry modal to a direct child of <body> on load.
        (function () {
            var modal = document.getElementById('{{ $modalId }}');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        })();
    </script>

    @once
        @push('footer')
            <script>
                document.addEventListener('submit', function (e) {
                    if (!e.target.classList.contains('js-enquiry-form')) return;
                    e.preventDefault();

                    var form = e.target;
                    var btn = form.querySelector('button[type=submit]');
                    var errorBox = form.querySelector('.enquiry-form-error');
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
                                errorBox.classList.add('d-none');
                                form.closest('.modal').querySelector('.modal-body').innerHTML =
                                    '<div class="alert alert-success mb-0">' + (result.json.message || 'Thank you!') + '</div>';
                                form.closest('.modal').querySelector('.modal-footer').remove();
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
        @endpush
    @endonce
@endif
