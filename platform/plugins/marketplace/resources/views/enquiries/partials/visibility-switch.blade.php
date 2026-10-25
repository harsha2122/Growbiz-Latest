@php
    $visible = $store->enquiryDetailsVisible();
@endphp

<div data-enquiry-visibility-control>
    <label class="form-check form-switch d-inline-flex align-items-center gap-2 mb-0">
        <input
            class="form-check-input m-0"
            type="checkbox"
            role="switch"
            data-enquiry-visibility-switch
            data-store-id="{{ $store->id }}"
            data-expires-at="{{ $visible ? $store->enquiryVisibility->expires_at->toIso8601String() : '' }}"
            aria-label="{{ __('Reveal enquiries to :store', ['store' => $store->name]) }}"
            @checked($visible)
        >
        <span data-visibility-status class="badge {{ $visible ? 'bg-success-lt' : 'bg-secondary-lt' }}" role="status" aria-live="polite">
            {{ $visible ? __('On') : __('Off') }}
        </span>
    </label>
    <small data-visibility-expiry class="text-muted d-block mt-1"></small>
    <small data-visibility-error class="d-none text-danger d-block mt-1" role="alert"></small>
</div>
