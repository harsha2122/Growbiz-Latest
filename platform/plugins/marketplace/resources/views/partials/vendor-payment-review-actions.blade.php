@once
    @push('footer')
        <div class="modal fade" id="vendor-payment-review-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="vendor-payment-review-form">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Review Payment') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="payment_id">
                            <input type="hidden" name="status">
                            <div class="mb-10">
                                <label class="form-label">{{ __('Remarks') }}</label>
                                <textarea class="form-control" name="remarks" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Confirm') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var modalEl = document.getElementById('vendor-payment-review-modal');
                var modal = new bootstrap.Modal(modalEl);
                var form = document.getElementById('vendor-payment-review-form');

                document.addEventListener('click', function (e) {
                    var btn = e.target.closest('.js-vendor-payment-review-btn');
                    if (!btn) return;

                    form.payment_id.value = btn.dataset.id;
                    form.status.value = btn.dataset.status;
                    form.remarks.value = '';
                    modal.show();
                });

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    var submitBtn = form.querySelector('button[type=submit]');
                    submitBtn.disabled = true;

                    fetch('{{ route('marketplace.vendor-payments.review', '__ID__') }}'.replace('__ID__', form.payment_id.value), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({
                            status: form.status.value,
                            remarks: form.remarks.value,
                        }),
                    })
                        .then(function (res) { return res.json(); })
                        .then(function (json) {
                            submitBtn.disabled = false;
                            modal.hide();
                            if (window.Botble && Botble.showSuccess) {
                                Botble.showSuccess(json.message || 'Updated.');
                            }
                            setTimeout(function () { location.reload(); }, 600);
                        })
                        .catch(function () {
                            submitBtn.disabled = false;
                            if (window.Botble && Botble.showError) {
                                Botble.showError('Something went wrong.');
                            }
                        });
                });
            })();
        </script>
    @endpush
@endonce

<div class="d-flex gap-1">
    <button type="button" class="btn btn-sm btn-success js-vendor-payment-review-btn" data-id="{{ $id }}" data-status="approved">
        {{ __('Approve') }}
    </button>
    <button type="button" class="btn btn-sm btn-danger js-vendor-payment-review-btn" data-id="{{ $id }}" data-status="declined">
        {{ __('Decline') }}
    </button>
</div>
