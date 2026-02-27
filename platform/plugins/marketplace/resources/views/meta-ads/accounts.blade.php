@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <h4>{{ __('Meta Ads – Connected Ad Accounts') }}</h4>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Store') }}</th>
                            <th>{{ __('FB User') }}</th>
                            <th>{{ __('Ad Account ID') }}</th>
                            <th>{{ __('Ad Account Name') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Token Expires') }}</th>
                            <th>{{ __('Connected At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            <tr>
                                <td>{{ $account->id }}</td>
                                <td>{{ $account->store?->name ?? '—' }}</td>
                                <td>{{ $account->fb_user_name ?? '—' }}</td>
                                <td><code>{{ $account->ad_account_id ?? '—' }}</code></td>
                                <td>{{ $account->ad_account_name ?? '—' }}</td>
                                <td>
                                    @if ($account->is_connected)
                                        <span class="badge bg-success">{{ __('Connected') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Disconnected') }}</span>
                                    @endif
                                </td>
                                <td>{{ $account->token_expires_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ $account->connected_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">{{ __('No connected accounts found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($accounts->hasPages())
                <div class="card-footer">{{ $accounts->links() }}</div>
            @endif
        </div>
    </div>
@endsection
