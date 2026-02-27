@php
    $activeSub = \Botble\Marketplace\Models\VendorSubscription::getActiveForStore($store->id);
    $history   = \Botble\Marketplace\Models\VendorSubscription::query()
        ->where('store_id', $store->id)
        ->with('plan', 'assignedBy')
        ->orderByDesc('id')
        ->limit(10)
        ->get();
    $plans = \Botble\Marketplace\Models\SubscriptionPlan::getActivePlans();
@endphp

<x-core::tab.pane id="tab_subscription">
    {{-- Current subscription --}}
    <div class="mb-4">
        <h6 class="fw-semibold mb-3">{{ __('Current Subscription') }}</h6>
        @if ($activeSub)
            <div class="d-flex flex-wrap gap-3">
                <div><span class="text-muted">{{ __('Plan') }}:</span> <strong>{{ $activeSub->plan?->name ?? '—' }}</strong></div>
                <div><span class="text-muted">{{ __('Status') }}:</span>
                    <span class="badge bg-{{ $activeSub->isActive() ? 'success' : 'danger' }}">{{ $activeSub->status_label }}</span>
                </div>
                <div><span class="text-muted">{{ __('Expires') }}:</span>
                    {{ $activeSub->expires_at ? $activeSub->expires_at->format('d M Y') : __('Never') }}
                </div>
                <div><span class="text-muted">{{ __('Days left') }}:</span>
                    {{ $activeSub->days_remaining < 0 ? __('Unlimited') : $activeSub->days_remaining }}
                </div>
            </div>
        @else
            <div class="alert alert-warning py-2 mb-0">{{ __('No active subscription.') }}</div>
        @endif
    </div>

    <hr>

    {{-- Assign / Renew form --}}
    <div class="mb-4">
        <h6 class="fw-semibold mb-3">{{ __('Assign / Renew Plan') }}</h6>
        <form action="{{ route('marketplace.store.assign-plan', $store->id) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Plan') }} <span class="text-danger">*</span></label>
                    <select name="plan_id" class="form-select" required>
                        <option value="">— {{ __('Select plan') }} —</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}"
                                {{ $activeSub?->plan_id == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} ({{ $plan->duration_text }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Start Date') }}</label>
                    <input type="date" name="starts_at" class="form-control"
                           value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <input type="text" name="notes" class="form-control"
                           placeholder="{{ __('Optional note') }}" maxlength="500">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </div>

    <hr>

    {{-- History --}}
    <div>
        <h6 class="fw-semibold mb-3">{{ __('Subscription History') }}</h6>
        @if ($history->isEmpty())
            <p class="text-muted">{{ __('No history.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Plan') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Starts') }}</th>
                            <th>{{ __('Expires') }}</th>
                            <th>{{ __('Assigned By') }}</th>
                            <th>{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $sub)
                            <tr>
                                <td>{{ $sub->plan?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $sub->isActive() ? 'success' : ($sub->status === 'cancelled' ? 'secondary' : 'danger') }}">
                                        {{ $sub->status_label }}
                                    </span>
                                </td>
                                <td>{{ $sub->starts_at?->format('d M Y') ?? '—' }}</td>
                                <td>{{ $sub->expires_at?->format('d M Y') ?? __('Never') }}</td>
                                <td>{{ $sub->assignedBy?->name ?? '—' }}</td>
                                <td>{{ $sub->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-core::tab.pane>
