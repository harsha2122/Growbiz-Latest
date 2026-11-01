<div class="refer-panel">
    <h2>{{ __('Enquiry & offline verification') }}</h2>
    <p class="refer-muted">{{ __('Check that an enquiry is genuine or that the receipt and customer confirmation support the offline activity. The receipt is private. A visit or QR scan alone is not proof of purchase.') }}</p>
    <div style="overflow-x:auto"><table class="refer-table"><thead><tr><th>{{ __('Store / customer') }}</th><th>{{ __('Evidence') }}</th><th>{{ __('Status / reward') }}</th><th>{{ __('Verification') }}</th></tr></thead><tbody>
    @forelse ($events as $event)
        <tr>
            <td>{{ $event->store?->name }}<div>{{ $event->referral->customer?->name }}</div><small>{{ $event->referral->customer?->email }}</small></td>
            <td>{{ __(ucwords(str_replace('_', ' ', $event->kind))) }}<div>{{ $event->reference }} · {{ $event->occurred_on->format('d M Y') }}</div>
                @if (($event->referral->terms['enquiry_trigger'] ?? '') === 'service_completed')<p class="refer-muted">{{ __('Service completion must be confirmed in Profile Reviews & Services before approval.') }}</p>@endif
                @if ($event->enquiry)<p>{{ $event->enquiry->name }} · {{ $event->enquiry->contact_number }}<br>{{ $event->enquiry->email }}<br>{{ $event->enquiry->address }}</p>@endif
                @if ($event->kind === 'offline_purchase')<div>{{ __('Eligible after refunds') }}: {{ format_price($event->eligibleAmount()) }}<br>{{ __('Tax') }}: {{ format_price($event->tax) }} · {{ __('Shipping') }}: {{ format_price($event->shipping) }}</div>@endif
                @if ($event->evidence_path)<a href="{{ route('marketplace.refer-activity.evidence', $event->id) }}">{{ __('Download private receipt') }}</a>@endif
                <details class="mt-2"><summary>{{ __('Audit trail') }}</summary>@foreach (DB::table('mp_referral_audits')->where('referral_id', $event->referral_id)->orderBy('id')->get() as $audit)<small class="d-block">{{ $audit->created_at }} · {{ $audit->actor_role }} #{{ $audit->actor_id }} · {{ str_replace('_', ' ', $audit->action) }} · {{ $audit->details }}</small>@endforeach</details>
            </td>
            <td>{{ __(ucwords(str_replace('_', ' ', $event->status))) }}<div>{{ format_price($event->referral->amount) }}</div><small>{{ $event->customer_confirmed_at ? __('Customer confirmed') : __('Customer confirmation pending') }}</small><p>{{ $event->reason }}</p></td>
            <td>
                @if (auth()->user()->hasPermission('marketplace.refer-earn.edit'))
                    @if (in_array($event->status, ['awaiting_customer', 'awaiting_admin']))
                        <form method="post" action="{{ route('marketplace.refer-activity.verify', $event->id) }}">@csrf
                            <label class="visually-hidden" for="review-reason-{{ $event->id }}">{{ __('Verification note') }}</label><input id="review-reason-{{ $event->id }}" name="reason" class="form-control mb-2" required maxlength="140" placeholder="{{ __('Verification note / rejection reason') }}">
                            @if ($event->status === 'awaiting_admin')<button class="refer-btn" type="submit" name="approve" value="1">{{ __('Approve') }}</button>@endif
                            <button class="btn btn-outline-danger" type="submit" name="approve" value="0">{{ __('Reject') }}</button>
                        </form>
                    @elseif ($event->status === 'approved' && $event->kind === 'offline_purchase')
                        <form method="post" action="{{ route('marketplace.refer-activity.refund', $event->id) }}">@csrf
                            <label class="form-label" for="refund-{{ $event->id }}">{{ __('Cumulative merchandise refund') }}</label><input class="form-control mb-2" id="refund-{{ $event->id }}" name="amount" type="number" step="0.01" min="{{ $event->refunded_amount }}" max="{{ (float)$event->subtotal - (float)$event->discount }}" value="{{ $event->refunded_amount }}" required>
                            <input class="form-control mb-2" name="reason" required maxlength="140" aria-label="{{ __('Refund reason') }}" placeholder="{{ __('Refund reason') }}"><button class="btn" type="submit">{{ __('Record refund') }}</button>
                        </form>
                    @endif
                @endif
            </td>
        </tr>
    @empty<tr><td colspan="4">{{ __('No enquiry or offline activity awaiting verification.') }}</td></tr>@endforelse
    </tbody></table></div>{{ $events->links() }}
</div>
