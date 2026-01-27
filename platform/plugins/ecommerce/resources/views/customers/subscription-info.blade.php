<div class="p-3">
    @if($subscriptionStatus['has_subscription'])
        <dl class="row mb-2">
            <dt class="col-sm-5">{{ __('Current Plan') }}</dt>
            <dd class="col-sm-7">
                <span class="badge bg-primary text-primary-fg">{{ $subscriptionStatus['plan_name'] }}</span>
            </dd>
        </dl>

        <dl class="row mb-2">
            <dt class="col-sm-5">{{ __('Products') }}</dt>
            <dd class="col-sm-7">
                <strong>{{ $subscriptionStatus['products_used'] }}</strong>
                /
                @if($subscriptionStatus['products_limit'] == 0)
                    <span class="text-success">{{ __('Unlimited') }}</span>
                @else
                    {{ $subscriptionStatus['products_limit'] }}
                @endif
            </dd>
        </dl>

        <dl class="row mb-2">
            <dt class="col-sm-5">{{ __('Valid Till') }}</dt>
            <dd class="col-sm-7">
                @if($subscriptionStatus['expires_at'])
                    @if($subscriptionStatus['is_expired'])
                        <span class="text-danger">
                            {{ $subscriptionStatus['expires_at']->format('Y-m-d') }}
                            ({{ __('Expired') }})
                        </span>
                    @elseif($subscriptionStatus['days_remaining'] <= 7)
                        <span class="text-warning">
                            {{ $subscriptionStatus['expires_at']->format('Y-m-d') }}
                            ({{ $subscriptionStatus['days_remaining'] }} {{ __('days left') }})
                        </span>
                    @else
                        <span class="text-success">
                            {{ $subscriptionStatus['expires_at']->format('Y-m-d') }}
                            ({{ $subscriptionStatus['days_remaining'] }} {{ __('days left') }})
                        </span>
                    @endif
                @else
                    <span class="text-success">{{ __('Never expires') }}</span>
                @endif
            </dd>
        </dl>

        @if($subscriptionStatus['products_limit'] > 0)
            <div class="progress mt-3" style="height: 8px;">
                @php
                    $percentage = min(100, ($subscriptionStatus['products_used'] / $subscriptionStatus['products_limit']) * 100);
                    $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                @endphp
                <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%"></div>
            </div>
            <small class="text-muted">
                {{ $subscriptionStatus['products_remaining'] }} {{ __('products remaining') }}
            </small>
        @endif
    @else
        <div class="alert alert-warning mb-0">
            <x-core::icon name="ti ti-alert-circle" />
            {{ __('No active subscription plan') }}
        </div>
    @endif
</div>
