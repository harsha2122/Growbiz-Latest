<script>
(function () {
    const btn = document.getElementById('estimate-audience-btn');
    const out = document.getElementById('estimate-audience-result');
    if (!btn) return;

    btn.addEventListener('click', function () {
        out.textContent = 'Estimating…';
        btn.disabled = true;

        const body = new URLSearchParams({
            targeting_age_min: document.querySelector('[name="targeting_age_min"]')?.value || 18,
            targeting_age_max: document.querySelector('[name="targeting_age_max"]')?.value || 65,
            targeting_genders: document.querySelector('[name="targeting_genders"]')?.value || 'all',
            targeting_locations: document.getElementById('targeting_locations_input')?.value || '[]',
            targeting_interests: document.getElementById('targeting_interests_input')?.value || '[]',
            optimization_goal: document.querySelector('[name="optimization_goal"]')?.value || 'REACH',
        });

        document.querySelectorAll('[name="placements[]"]:checked').forEach(function (el) {
            body.append('placements[]', el.value);
        });

        fetch('{{ route('marketplace.vendor.meta-ads.ad-sets.delivery-estimate') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
            },
            body: body.toString(),
        })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (data.error) {
                    out.innerHTML = '<span class="text-muted small">' + data.error + '</span>';
                    return;
                }
                const lower = data.estimate_mau_lower;
                const upper = data.estimate_mau_upper;
                if (lower == null && upper == null) {
                    out.innerHTML = '<span class="text-muted small">Not enough data to estimate yet.</span>';
                    return;
                }
                out.innerHTML = '<span class="text-primary">'
                    + Number(lower || 0).toLocaleString() + ' – ' + Number(upper || 0).toLocaleString()
                    + ' people</span> <span class="text-muted small">potential monthly reach</span>';
            })
            .catch(function () {
                btn.disabled = false;
                out.innerHTML = '<span class="text-muted small">Could not fetch estimate.</span>';
            });
    });
})();
</script>
