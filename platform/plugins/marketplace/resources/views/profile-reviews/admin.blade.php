@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @if (session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
    <div class="alert alert-info">{{ __('Login is required for ratings. Stars do not require a purchase. Written reviews require a completed purchase or a confirmed service. Service bookings through WhatsApp must be confirmed here after completion.') }}</div>
    @if (auth()->user()->hasPermission('marketplace.profile-reviews.edit'))
        <div class="card mb-4">
            <div class="card-header"><h3 class="card-title">{{ __('Confirm a completed service') }}</h3></div>
            <div class="card-body">
                <form method="post" action="{{ route('marketplace.profile-reviews.confirm-service') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="service-store">{{ __('Vendor') }}</label><select id="service-store" class="form-select" name="store_id" required><option value="">{{ __('Choose vendor') }}</option>@foreach ($stores as $id => $name)<option value="{{ $id }}" @selected(old('store_id') == $id)>{{ $name }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label" for="service-customer">{{ __('Customer account email') }}</label><input id="service-customer" type="email" class="form-control" name="customer_email" required value="{{ old('customer_email') }}"></div>
                        <div class="col-md-6"><label class="form-label" for="service-product">{{ __('Service (optional)') }}</label><select id="service-product" class="form-select" name="product_id"><option value="">{{ __('Profile service — no listed product') }}</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-store="{{ $product->store_id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} · {{ $product->store?->name }}</option>@endforeach</select><small class="text-muted">{{ __('Choose a listed service to also unlock a review on its product page.') }}</small></div>
                        <div class="col-md-3"><label class="form-label" for="service-date">{{ __('Completion date') }}</label><input id="service-date" type="date" class="form-control" name="completed_at" max="{{ today()->toDateString() }}" required value="{{ old('completed_at', today()->toDateString()) }}"></div>
                        <div class="col-md-3"><label class="form-label" for="service-reference">{{ __('Booking / invoice reference') }}</label><input id="service-reference" class="form-control" name="reference" maxlength="191" required value="{{ old('reference') }}"></div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">{{ __('Confirm service completed') }}</button>
                </form>
            </div>
        </div>
    @endif
    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title">{{ __('Profile ratings & reviews') }}</h3></div>
        <div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Vendor') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Review') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
            @forelse ($reviews as $review)
                <tr><td>{{ $review->store?->name }}</td><td>{{ $review->customer?->name }}</td><td>{{ $review->star }}/5</td><td style="min-width:220px;max-width:450px;white-space:pre-line;overflow-wrap:anywhere">{{ $review->comment ?: __('Stars only') }}</td><td>
                    @if (auth()->user()->hasPermission('marketplace.profile-reviews.edit'))
                        <form method="post" action="{{ route('marketplace.profile-reviews.moderate', $review->id) }}">@csrf<div class="d-flex gap-2"><select name="status" class="form-select" aria-label="{{ __('Review status') }}">@foreach (['published' => __('Published'), 'pending' => __('Pending'), 'rejected' => __('Rejected')] as $value => $label)<option value="{{ $value }}" @selected($review->status === $value)>{{ $label }}</option>@endforeach</select><button type="submit" class="btn">{{ __('Save') }}</button></div></form>
                    @else
                        {{ $review->status }}
                    @endif
                </td></tr>
            @empty
                <tr><td colspan="5">{{ __('No profile ratings yet.') }}</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="card-footer">{{ $reviews->links() }}</div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ __('Confirmed services') }}</h3></div>
        <div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Vendor') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Completed') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Action') }}</th></tr></thead><tbody>
            @forelse ($services as $service)
                <tr><td>{{ $service->store?->name }}</td><td>{{ $service->customer?->email }}</td><td>{{ $service->completed_at->format('d M Y') }}</td><td>{{ $service->reference }}<small class="d-block text-muted">{{ __('Confirmed by admin #:id', ['id' => $service->verified_by]) }}</small></td><td>
                    @if (auth()->user()->hasPermission('marketplace.profile-reviews.edit'))
                        <form method="post" action="{{ route('marketplace.profile-reviews.revoke-service', $service->id) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">{{ __('Revoke confirmation') }}</button></form>
                    @endif
                </td></tr>
            @empty
                <tr><td colspan="5">{{ __('No confirmed services yet.') }}</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="card-footer">{{ $services->links() }}</div>
    </div>
    <script>
        (function () {
            var store = document.getElementById('service-store');
            var product = document.getElementById('service-product');
            if (!store || !product) return;
            function filter() {
                product.querySelectorAll('[data-store]').forEach(function (option) {
                    option.hidden = option.dataset.store !== store.value;
                    option.disabled = option.hidden;
                    if (option.selected && option.disabled) product.value = '';
                });
            }
            store.addEventListener('change', filter);
            filter();
        })();
    </script>
@endsection
