{{--
  Location Picker — reusable partial for ad set create/edit.
  Props:
    $existingLocations — array of structured location objects (or empty array)
--}}
@php $existingLocations = $existingLocations ?? []; @endphp

<div class="mb-3" id="location-picker-wrap">
    <label class="form-label">Locations</label>

    {{-- Hidden field that gets submitted --}}
    <input type="hidden" name="targeting_locations" id="targeting_locations_input"
           value="{{ json_encode($existingLocations) }}">

    {{-- Tag display --}}
    <div id="location-tags" class="d-flex flex-wrap gap-2 mb-2">
        @foreach($existingLocations as $loc)
            @php
                $label = is_array($loc)
                    ? ($loc['name'] ?? '') . (isset($loc['region']) ? ', ' . $loc['region'] : '') . ' (' . ucfirst($loc['type'] ?? '') . ')'
                    : strtoupper($loc);
            @endphp
            <span class="badge bg-primary d-flex align-items-center gap-1 py-2 px-2 location-tag"
                  data-loc="{{ json_encode($loc) }}">
                {{ $label }}
                <button type="button" class="btn-close btn-close-white ms-1" style="font-size:.6rem"
                        onclick="removeLocation(this)"></button>
            </span>
        @endforeach
    </div>

    {{-- Search input --}}
    <div class="position-relative">
        <input type="text" id="location_search" class="form-control"
               placeholder="Search country, state, city or pin code…" autocomplete="off">
        <div id="location_suggestions" class="position-absolute w-100 bg-white border rounded shadow-sm z-3 d-none"
             style="top:100%; max-height:240px; overflow-y:auto; z-index:1050;"></div>
    </div>
    <div class="form-text">Search and select locations. Supports countries, states/regions, cities and pin codes.</div>
</div>

<script>
(function () {
    const searchUrl  = '{{ route('marketplace.vendor.meta-ads.ad-sets.search-locations') }}';
    const searchInput = document.getElementById('location_search');
    const suggestions = document.getElementById('location_suggestions');
    const tagsWrap    = document.getElementById('location-tags');
    const hidden      = document.getElementById('targeting_locations_input');

    let locations = @json($existingLocations);
    let debounceTimer;

    function syncHidden() {
        hidden.value = JSON.stringify(locations);
    }

    function renderTags() {
        tagsWrap.innerHTML = '';
        locations.forEach((loc, idx) => {
            const label = typeof loc === 'string'
                ? loc.toUpperCase()
                : (loc.name || '') + (loc.region ? ', ' + loc.region : '') + ' (' + capitalize(loc.type || '') + ')';

            const span = document.createElement('span');
            span.className = 'badge bg-primary d-flex align-items-center gap-1 py-2 px-2';
            span.innerHTML = `${escHtml(label)}
                <button type="button" class="btn-close btn-close-white ms-1" style="font-size:.6rem"
                        data-idx="${idx}" onclick="window._removeLocationIdx(${idx})"></button>`;
            tagsWrap.appendChild(span);
        });
    }

    window._removeLocationIdx = function (idx) {
        locations.splice(idx, 1);
        syncHidden();
        renderTags();
    };

    window.removeLocation = function (btn) {
        const tag = btn.closest('.location-tag');
        const loc = JSON.parse(tag.dataset.loc);
        locations = locations.filter(l => JSON.stringify(l) !== JSON.stringify(loc));
        syncHidden();
        renderTags();
    };

    function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function isDuplicate(loc) {
        return locations.some(l =>
            (typeof l === 'object' && typeof loc === 'object' && l.key === loc.key && l.type === loc.type)
            || JSON.stringify(l) === JSON.stringify(loc)
        );
    }

    function addLocation(loc) {
        if (isDuplicate(loc)) return;
        locations.push(loc);
        syncHidden();
        renderTags();
    }

    function showSuggestions(items) {
        suggestions.innerHTML = '';
        if (! items.length) {
            suggestions.innerHTML = '<div class="px-3 py-2 text-muted small">No results found.</div>';
            suggestions.classList.remove('d-none');
            return;
        }
        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'px-3 py-2 suggestion-item';
            div.style.cursor = 'pointer';
            div.innerHTML = `<span class="fw-semibold">${escHtml(item.name)}</span>
                <small class="text-muted ms-1">${escHtml(item.label.split(item.name).slice(1).join(item.name))}</small>
                <span class="badge bg-secondary ms-2" style="font-size:.65rem">${capitalize(item.type)}</span>`;
            div.addEventListener('mousedown', (e) => {
                e.preventDefault();
                addLocation({ key: item.key, type: item.type, name: item.name,
                               region: item.region || null, country_code: item.country_code || null });
                searchInput.value = '';
                suggestions.classList.add('d-none');
            });
            div.addEventListener('mouseenter', () => div.classList.add('bg-light'));
            div.addEventListener('mouseleave', () => div.classList.remove('bg-light'));
            suggestions.appendChild(div);
        });
        suggestions.classList.remove('d-none');
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (q.length < 2) { suggestions.classList.add('d-none'); return; }
        debounceTimer = setTimeout(() => {
            fetch(searchUrl + '?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => showSuggestions(Array.isArray(data) ? data : []))
                .catch(() => suggestions.classList.add('d-none'));
        }, 300);
    });

    searchInput.addEventListener('blur', () => {
        setTimeout(() => suggestions.classList.add('d-none'), 150);
    });
})();
</script>
