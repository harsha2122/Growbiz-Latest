@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <strong class="fs-3">{{ number_format($summary['average'], 1) }}/5</strong>
                <span class="text-muted">{{ trans_choice(':count published rating|:count published ratings', $summary['count'], ['count' => $summary['count']]) }}</span>
            </div>
            <p class="text-muted mb-0 mt-2">{{ __('Star ratings are open to signed-in customers. Written reviews require a completed purchase or an admin-confirmed service.') }}</p>
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
