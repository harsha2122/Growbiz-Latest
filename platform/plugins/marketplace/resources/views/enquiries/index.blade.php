@extends('core/table::table')

@section('content')
    @parent

    <script>
        (function () {
            var labels = {{ Illuminate\Support\Js::from([
                'on' => __('On'),
                'off' => __('Off'),
                'saving' => __('Saving…'),
                'error' => __('Unable to save. Please try again.'),
            ]) }};

            // Delegation keeps switches working after DataTables pagination, search or reload.
            document.addEventListener('change', async function (event) {
                var toggle = event.target;
                if (!toggle.matches('[data-enquiry-visibility-switch]')) return;

                var control = toggle.closest('[data-enquiry-visibility-control]');
                var status = control.querySelector('[data-visibility-status]');
                var errorBox = control.querySelector('[data-visibility-error]');
                var previous = !toggle.checked;
                toggle.disabled = true;
                status.textContent = labels.saving;
                errorBox.classList.add('d-none');

                try {
                    var response = await fetch({{ Illuminate\Support\Js::from(route('marketplace.enquiries.toggle-visibility')) }}, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': {{ Illuminate\Support\Js::from(csrf_token()) }},
                        },
                        body: JSON.stringify({store_id: Number(toggle.dataset.storeId), visible: toggle.checked ? 1 : 0}),
                    });
                    var result = await response.json();

                    if (!response.ok || result.error || typeof result.data?.visible !== 'boolean') {
                        throw new Error(result.message || labels.error);
                    }

                    toggle.checked = result.data.visible;
                } catch (error) {
                    toggle.checked = previous;
                    errorBox.textContent = error.message || labels.error;
                    errorBox.classList.remove('d-none');
                } finally {
                    toggle.disabled = false;
                    status.textContent = toggle.checked ? labels.on : labels.off;
                    status.classList.toggle('bg-success-lt', toggle.checked);
                    status.classList.toggle('bg-secondary-lt', !toggle.checked);
                }
            });
        })();
    </script>
@endsection
