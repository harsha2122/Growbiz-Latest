@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">{{ __('Enquiries') }}</h4>
    </div>

    <div class="alert alert-info">
        {{ __('You have :count enquiries so far.', ['count' => $count]) }}
    </div>

    @if (! $visible)
        <div class="card">
            <div class="card-body text-center py-5">
                <x-core::icon name="ti ti-lock" style="font-size: 32px;" />
                <p class="mt-3 mb-0 text-muted">
                    {{ __('Enquiry details are currently hidden. The admin will reveal contact details once your account is approved for this.') }}
                </p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Contact') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Address') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Source') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enquiries as $enquiry)
                            <tr>
                                <td>{{ $enquiry->name }}</td>
                                <td>{{ $enquiry->contact_number }}</td>
                                <td>{{ $enquiry->email ?: '—' }}</td>
                                <td>{{ $enquiry->address ?: '—' }}</td>
                                <td>{{ $enquiry->product?->name ?? '—' }}</td>
                                <td>
                                    @if ($enquiry->source === 'whatsapp')
                                        {{ __('WhatsApp') }}
                                    @elseif ($enquiry->source === 'product_page')
                                        {{ __('Product Page') }}
                                    @else
                                        {{ __('Vendor Page') }}
                                    @endif
                                </td>
                                <td>{{ $enquiry->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No enquiries yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $enquiries->links() }}</div>
    @endif
@endsection
