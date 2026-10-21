@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">{{ __('Payments') }}</h4>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>{{ __('Scan to Pay') }}</strong>
                </div>
                <div class="card-body text-center">
                    @if ($qrCode)
                        <img src="{{ RvMedia::getImageUrl($qrCode) }}" alt="{{ __('Payment QR Code') }}" style="max-width: 260px; width: 100%;">
                        <p class="text-muted mt-3 mb-0">
                            {{ __('Scan this code to make your payment, then submit the transaction details below for admin approval.') }}
                        </p>
                    @else
                        <p class="text-muted mb-0">{{ __('No payment QR code has been set up by the admin yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>{{ __('Submit a Payment') }}</strong>
                </div>
                <div class="card-body">
                    <form class="js-vendor-payment-form" data-url="{{ route('marketplace.vendor.payments.store') }}">
                        <div class="mb-10">
                            <label class="form-label">{{ __('Transaction ID') }}</label>
                            <input type="text" class="form-control" name="transaction_id" required>
                        </div>
                        <div class="mb-10">
                            <label class="form-label">{{ __('Amount') }}</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="amount" required>
                        </div>
                        <div class="mb-10">
                            <label class="form-label">{{ __('Payment Date') }}</label>
                            <input type="date" class="form-control" name="payment_date" max="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="vendor-payment-form-error text-danger small d-none mb-10"></div>
                        <button type="submit" class="btn btn-primary">{{ __('Submit for Approval') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>{{ __('Your Submitted Payments') }}</strong>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Transaction ID') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Payment Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Remarks') }}</th>
                        <th>{{ __('Submitted On') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments ?? [] as $payment)
                        <tr>
                            <td>{{ $payment->transaction_id }}</td>
                            <td>{{ format_price($payment->amount) }}</td>
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>{!! $payment->status->toHtml() !!}</td>
                            <td>{{ $payment->remarks ?: '—' }}</td>
                            <td>{{ $payment->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No payments submitted yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if (isset($payments) && $payments->hasPages())
        <div class="mt-3">{{ $payments->links() }}</div>
    @endif

    @push('footer')
        <script>
            document.addEventListener('submit', function (e) {
                if (!e.target.classList.contains('js-vendor-payment-form')) return;
                e.preventDefault();

                var form = e.target;
                var btn = form.querySelector('button[type=submit]');
                var errorBox = form.querySelector('.vendor-payment-form-error');
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
                            location.reload();
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
@endsection
