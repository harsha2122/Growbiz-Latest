<div style="overflow-x:auto">
<table class="refer-table">
    <thead><tr><th>{{ __('Referral') }}</th><th>{{ isset($seller) ? __('Referrer') : __('Store') }}</th><th>{{ __('Reward') }}</th><th>{{ __('Status') }}</th><th>{{ __('Details') }}</th></tr></thead>
    <tbody>
    @forelse ($referrals as $referral)
        <tr>
            <td>#{{ $referral->id }}<div class="refer-muted">{{ $referral->created_at->format('d M Y') }}</div></td>
            <td>@if (isset($seller)){{ $referral->referrer?->name ?? __('Deleted account') }}<div class="refer-muted">{{ $referral->referrer?->email }}</div>@else{{ $referral->store?->name }}@endif</td>
            <td>{{ format_price($referral->amount) }}</td>
            <td><span class="refer-badge">{{ __(ucfirst($referral->status)) }}</span>@if ($referral->payout_requested_at)<div class="refer-muted">{{ __('Payment requested') }}</div>@endif</td>
            <td>
                @if ($referral->order_id)<div>{{ __('Order') }} #{{ $referral->order?->code ?? $referral->order_id }}</div>@endif
                @if ($referral->available_at && $referral->status === 'pending')<div class="refer-muted">{{ __('Available after') }} {{ $referral->available_at->format('d M Y, H:i') }}</div>@endif
                @if ($referral->status === 'pending' && ! $referral->available_at)<div class="refer-muted">{{ __('Awaiting delivery, payment, or return review') }}</div>@endif
                @if ($referral->reason)<div class="refer-muted">{{ $referral->reason }}</div>@endif
                @if ($referral->paid_at)<div class="refer-muted">{{ __('Paid') }} {{ $referral->paid_at->format('d M Y') }} · {{ $referral->payout_reference }}</div>@endif
                @if (isset($seller) && $referral->status === 'available')
                    <form method="post" action="{{ route('marketplace.vendor.refer-earn.payout', $referral->id) }}" class="refer-actions">@csrf
                        <input class="form-control" style="max-width:220px" name="reference" maxlength="191" required aria-label="{{ __('Payment reference') }}" placeholder="{{ __('Payment reference') }}">
                        <button class="refer-btn" type="submit">{{ __('Record payment') }}</button>
                    </form>
                @elseif (! isset($seller) && $referral->status === 'available' && ! $referral->payout_requested_at)
                    <form method="post" action="{{ route('public.referral.request-payment', $referral->id) }}" class="refer-actions">@csrf<button class="refer-btn" type="submit">{{ __('Request payment') }}</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="refer-muted">{{ __('No referrals yet.') }}</td></tr>
    @endforelse
    </tbody>
</table>
</div>
