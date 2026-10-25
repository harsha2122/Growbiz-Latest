<x-core::card class="mb-3">
    <x-core::card.body>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <p class="fw-semibold mb-1">{{ __('Reveal enquiries to vendor') }}</p>
                <p class="text-muted mb-0">{{ $store->name }}</p>
            </div>
            @include('plugins/marketplace::enquiries.partials.visibility-switch')
        </div>
    </x-core::card.body>
</x-core::card>
