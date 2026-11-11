@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @if (session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card mb-3">
        <div class="card-body">
            <h4>{{ __('Confirm a completed service') }}</h4>
            <p class="text-muted">{{ __('Confirm only services your business has actually completed. The customer must have a verified Growbiz account. This unlocks written reviews; star ratings never require a service.') }}</p>
            <form method="post" action="{{ route('marketplace.vendor.reviews.confirm-service') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5"><label for="service-customer-email" class="form-label">{{ __('Customer account email') }}</label><input id="service-customer-email" class="form-control" name="customer_email" type="email" value="{{ old('customer_email') }}" required></div>
                    <div class="col-md-3"><label for="service-completed-at" class="form-label">{{ __('Completion date') }}</label><input id="service-completed-at" class="form-control" name="completed_at" type="date" max="{{ today()->toDateString() }}" value="{{ old('completed_at', today()->toDateString()) }}" required></div>
                    <div class="col-md-4"><label for="service-reference" class="form-label">{{ __('Invoice / service reference') }}</label><input id="service-reference" class="form-control" name="reference" maxlength="191" value="{{ old('reference') }}" required></div>
                </div>
                <button class="btn btn-primary mt-3" type="submit">{{ __('Confirm completed service') }}</button>
            </form>
            @if ($services->isNotEmpty())
                <details class="mt-3"><summary>{{ __('Confirmed services') }}</summary>
                    <div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Date') }}</th><th>{{ __('Confirmed by') }}</th></tr></thead><tbody>
                    @foreach ($services as $service)<tr><td>{{ $service->customer?->name }}</td><td>{{ $service->reference }}</td><td>{{ $service->completed_at->format('d M Y') }}</td><td>{{ $service->verified_by_seller ? __('Seller') : __('Admin') }}</td></tr>@endforeach
                    </tbody></table></div>{{ $services->links() }}
                </details>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <strong class="fs-3">{{ number_format($summary['average'], 1) }}/5</strong>
                <span class="text-muted">{{ trans_choice(':count published rating|:count published ratings', $summary['count'], ['count' => $summary['count']]) }}</span>
            </div>
            <p class="text-muted mb-0 mt-2">{{ __('Star ratings are open to signed-in customers. Written reviews require a completed purchase or an seller/admin-confirmed service.') }}</p>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Review') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>{{ $review->display_name }}</td>
                            <td>{{ $review->star }}/5</td>
                            <td style="min-width:200px;max-width:450px;white-space:pre-line;overflow-wrap:anywhere">{{ $review->comment ?: __('Stars only') }}</td>
                            <td>{{ __(ucfirst($review->status)) }}</td>
                            <td>{{ $review->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">{{ __('No profile ratings yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $reviews->links() }}</div>
    </div>
@endsection
