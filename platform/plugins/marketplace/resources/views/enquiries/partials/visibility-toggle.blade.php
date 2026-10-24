@php
    $visible = $store->enquiryDetailsVisible();
@endphp

<x-core::card class="mb-3">
    <x-core::card.body>
        <form id="enquiry-visibility-form" action="{{ route('marketplace.enquiries.toggle-visibility') }}" method="post">
            @csrf
            <input type="hidden" name="store_id" value="{{ $store->id }}">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <label for="reveal-vendor-enquiries" class="fw-semibold mb-1">{{ __('Reveal enquiries to vendor') }}</label>
                    <p id="enquiry-visibility-help" class="text-muted mb-0">
                        {{ __('Allow :store to see customer contact information and enquiry details in their seller dashboard.', ['store' => $store->name]) }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span id="enquiry-visibility-status" class="badge {{ $visible ? 'bg-success-lt' : 'bg-secondary-lt' }}" role="status" aria-live="polite">
                        {{ $visible ? __('Visible to vendor') : __('Hidden from vendor') }}
                    </span>
                    <label class="form-check form-switch mb-0">
                        <input
                            id="reveal-vendor-enquiries"
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            name="visible"
                            value="1"
                            aria-labelledby="enquiry-visibility-label"
                            aria-describedby="enquiry-visibility-help"
                            @checked($visible)
                        >
                        <span id="enquiry-visibility-label" class="visually-hidden">{{ __('Reveal enquiries to vendor') }}</span>
                    </label>
                </div>
            </div>
            <div id="enquiry-visibility-error" class="alert alert-danger mt-3 mb-0 d-none" role="alert"></div>
        </form>
    </x-core::card.body>
</x-core::card>

<script>
    (function () {
        var form = document.getElementById('enquiry-visibility-form');
        var toggle = form.querySelector('[name="visible"]');
        var status = document.getElementById('enquiry-visibility-status');
        var errorBox = document.getElementById('enquiry-visibility-error');
        var saved = toggle.checked;
        var labels = {{ Illuminate\Support\Js::from([
            'visible' => __('Visible to vendor'),
            'hidden' => __('Hidden from vendor'),
            'saving' => __('Saving…'),
            'error' => __('Unable to save. Please try again.'),
        ]) }};

        function showStatus() {
            status.textContent = saved ? labels.visible : labels.hidden;
            status.classList.toggle('bg-success-lt', saved);
            status.classList.toggle('bg-secondary-lt', !saved);
        }

        form.addEventListener('submit', function (event) { event.preventDefault(); });

        toggle.addEventListener('change', async function () {
            toggle.disabled = true;
            status.textContent = labels.saving;
            errorBox.classList.add('d-none');

            try {
                var response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                    },
                    body: JSON.stringify({store_id: {{ (int) $store->id }}, visible: toggle.checked ? 1 : 0}),
                });
                var result = await response.json();

                if (!response.ok || result.error || typeof result.data?.visible !== 'boolean') {
                    throw new Error(result.message || labels.error);
                }

                saved = result.data.visible;
                toggle.checked = saved;
            } catch (error) {
                toggle.checked = saved;
                errorBox.textContent = error.message || labels.error;
                errorBox.classList.remove('d-none');
            } finally {
                toggle.disabled = false;
                showStatus();
            }
        });
    })();
</script>
