@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @include('plugins/marketplace::referral-program.styles')

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if (session('success_msg'))
        <div class="alert alert-success">{{ session('success_msg') }}</div>
    @endif

    <div class="refer-panel">
        <h2>{{ __('Seller referral programs') }}</h2>
        <p class="refer-muted">{{ __('Seller-funded programs. Payments are recorded by sellers after an external payment; no automatic money transfer occurs.') }}</p>
        <div style="overflow-x:auto">
            <table class="refer-table">
                <thead>
                    <tr><th>{{ __('Store') }}</th><th>{{ __('Program') }}</th><th>{{ __('Budget / committed') }}</th><th>{{ __('Action') }}</th></tr>
                </thead>
                <tbody>
                    @forelse ($programs as $program)
                        <tr>
                            <td>{{ $program->store?->name }}</td>
                            <td>{{ $program->mode === 'visit' ? __('Verified visits') : __('Delivered purchases') }} · {{ $program->isActive() ? __('Active') : __('Paused / expired') }}</td>
                            <td>{{ format_price($program->budget) }} / {{ format_price($program->reserved) }}</td>
                            <td>
                                @if ($program->enabled && auth()->user()->hasPermission('marketplace.refer-earn.edit'))
                                    <form method="post" action="{{ route('marketplace.refer-earn.pause', $program->id) }}">
                                        @csrf
                                        <button class="refer-btn" type="submit">{{ __('Pause') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('No programs yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $programs->links() }}
    </div>

    <div class="refer-panel">
        <h2>{{ __('Referral ledger') }}</h2>
        <div style="overflow-x:auto">
            <table class="refer-table">
                <thead>
                    <tr><th>{{ __('ID / Store') }}</th><th>{{ __('Referrer') }}</th><th>{{ __('Order') }}</th><th>{{ __('Reward / status') }}</th><th>{{ __('Payment / notes') }}</th></tr>
                </thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        <tr>
                            <td>#{{ $referral->id }} · {{ $referral->store?->name }}</td>
                            <td>{{ $referral->referrer?->name }}<br>{{ $referral->referrer?->email }}</td>
                            <td>{{ $referral->order?->code ?? '—' }}</td>
                            <td>{{ format_price($referral->amount) }} · {{ $referral->status }}</td>
                            <td>
                                {{ $referral->payout_reference }} {{ $referral->reason }}
                                @if (in_array($referral->status, ['tracked', 'pending', 'available']) && auth()->user()->hasPermission('marketplace.refer-earn.edit'))
                                    <form method="post" action="{{ route('marketplace.refer-earn.reject', $referral->id) }}" class="refer-actions">
                                        @csrf
                                        <input class="form-control" name="reason" maxlength="140" required aria-label="{{ __('Rejection reason') }}" placeholder="{{ __('Rejection reason') }}">
                                        <button class="refer-btn" type="submit">{{ __('Reject reward') }}</button>
                                    </form>
                                @endif
                                @if ($referral->payout_requested_at)
                                    <div>{{ __('Payment requested') }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">{{ __('No referrals yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $referrals->links() }}
    </div>
@endsection
