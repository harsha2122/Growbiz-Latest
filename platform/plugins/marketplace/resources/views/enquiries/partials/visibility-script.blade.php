<dialog id="enquiry-duration-dialog" aria-labelledby="enquiry-duration-title" style="border:0;border-radius:12px;padding:24px;width:min(420px,calc(100% - 32px));box-shadow:0 12px 48px #0003;">
    <form method="dialog" id="enquiry-duration-form">
        <h3 id="enquiry-duration-title" class="mb-2">{{ __('Reveal enquiries') }}</h3>
        <p class="text-muted">{{ __('Choose how long this vendor can view enquiry details.') }}</p>
        <label for="enquiry-duration" class="form-label">{{ __('Duration') }}</label>
        <select id="enquiry-duration" class="form-select mb-3">
            <option value="24">{{ __('1 day') }}</option>
            <option value="168">{{ __('7 days') }}</option>
            <option value="720">{{ __('30 days') }}</option>
            <option value="8760">{{ __('1 year') }}</option>
            <option value="custom">{{ __('Custom hours') }}</option>
        </select>
        <div id="enquiry-custom-duration" hidden>
            <label for="enquiry-duration-hours" class="form-label">{{ __('Number of hours') }}</label>
            <input id="enquiry-duration-hours" class="form-control mb-3" type="number" min="1" max="8760" step="1" value="24" disabled>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" id="enquiry-duration-cancel" class="btn">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Reveal enquiries') }}</button>
        </div>
    </form>
</dialog>
<style>#enquiry-duration-dialog::backdrop { background: #0006; }</style>
<script>
(function () {
    var dialog = document.getElementById('enquiry-duration-dialog');
    var form = document.getElementById('enquiry-duration-form');
    var duration = document.getElementById('enquiry-duration');
    var hours = document.getElementById('enquiry-duration-hours');
    var pending = null;
    var labels = {{ Illuminate\Support\Js::from([
        'on' => __('On'), 'off' => __('Off'), 'saving' => __('Saving…'),
        'until' => __('Visible until'), 'error' => __('Unable to save. Please try again.'),
    ]) }};
    function refresh(toggle) {
        var control = toggle.closest('[data-enquiry-visibility-control]');
        var expiry = new Date(toggle.dataset.expiresAt);
        if (toggle.checked && (!Number.isFinite(expiry.getTime()) || expiry <= new Date())) toggle.checked = false;
        var status = control.querySelector('[data-visibility-status]');
        status.textContent = toggle.checked ? labels.on : labels.off;
        status.classList.toggle('bg-success-lt', toggle.checked);
        status.classList.toggle('bg-secondary-lt', !toggle.checked);
        control.querySelector('[data-visibility-expiry]').textContent = toggle.checked ? labels.until + ' ' + expiry.toLocaleString() : '';
    }
    function refreshAll() {
        document.querySelectorAll('[data-enquiry-visibility-switch]:not(:disabled)').forEach(refresh);
    }
    duration.addEventListener('change', function () {
        var custom = duration.value === 'custom';
        document.getElementById('enquiry-custom-duration').hidden = !custom;
        hours.disabled = !custom;
        hours.required = custom;
    });
    document.getElementById('enquiry-duration-cancel').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('close', function () { pending = null; });
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var toggle = pending;
        var amount = Number(duration.value === 'custom' ? hours.value : duration.value);
        if (!toggle || !Number.isInteger(amount) || amount < 1 || amount > 8760) return;
        dialog.close();
        save(toggle, true, amount);
    });
    async function save(toggle, visible, amount) {
        var previous = toggle.checked;
        var control = toggle.closest('[data-enquiry-visibility-control]');
        var errorBox = control.querySelector('[data-visibility-error]');
        toggle.disabled = true;
        control.querySelector('[data-visibility-status]').textContent = labels.saving;
        errorBox.classList.add('d-none');
        try {
            var response = await fetch({{ Illuminate\Support\Js::from(route('marketplace.enquiries.toggle-visibility')) }}, {
                method: 'POST', credentials: 'same-origin',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': {{ Illuminate\Support\Js::from(csrf_token()) }}},
                body: JSON.stringify({store_id: Number(toggle.dataset.storeId), visible: visible ? 1 : 0, duration_hours: amount}),
            });
            var result = await response.json();
            if (!response.ok || result.error || typeof result.data?.visible !== 'boolean') throw new Error(result.message || labels.error);
            toggle.checked = result.data.visible;
            toggle.dataset.expiresAt = result.data.expires_at || '';
        } catch (error) {
            toggle.checked = previous;
            errorBox.textContent = error.message || labels.error;
            errorBox.classList.remove('d-none');
        } finally {
            toggle.disabled = false;
            refresh(toggle);
        }
    }
    document.addEventListener('change', function (event) {
        var toggle = event.target;
        if (!toggle.matches('[data-enquiry-visibility-switch]')) return;
        var enable = toggle.checked;
        toggle.checked = !enable;
        if (enable) {
            pending = toggle;
            dialog.showModal();
        } else {
            save(toggle, false, null);
        }
    });
    setInterval(refreshAll, 1000);
    refreshAll();
})();
</script>
