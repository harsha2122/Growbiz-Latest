{{--
  Location Picker — tab-based: Country → State → City → Pincode
  Props: $existingLocations (array of structured {key,type,name} objects)
--}}
@php $existingLocations = $existingLocations ?? []; @endphp

<div class="mb-3">
    <label class="form-label fw-semibold">Location Targeting</label>

    {{-- Hidden field submitted with the form --}}
    <input type="hidden" name="targeting_locations" id="targeting_locations_input"
           value="{{ json_encode($existingLocations) }}">

    {{-- Selected location tags --}}
    <div id="location-tags" class="d-flex flex-wrap gap-2 mb-3 min-height-32">
        @foreach($existingLocations as $loc)
            @php
                $isStr = is_string($loc);
                $type  = $isStr ? 'country' : ($loc['type'] ?? 'country');
                $label = $isStr ? strtoupper($loc) : (
                    ($loc['name'] ?? '') .
                    (!empty($loc['region'])       ? ', ' . $loc['region']       : '') .
                    (!empty($loc['country_name']) ? ', ' . $loc['country_name'] : '')
                );
                $typeColors = ['country' => 'primary', 'region' => 'info', 'city' => 'success', 'zip' => 'secondary'];
                $color = $typeColors[$type] ?? 'primary';
            @endphp
            <span class="badge bg-{{ $color }} d-flex align-items-center gap-1 px-2 py-2 fs-xs location-tag"
                  data-loc="{{ json_encode($isStr ? ['key' => $loc, 'type' => 'country', 'name' => strtoupper($loc)] : $loc) }}">
                <i class="ti ti-map-pin me-1" style="font-size:.75rem"></i>
                {{ $label }}
                <span class="ms-1 opacity-75" style="font-size:.65rem">({{ ucfirst($type) }})</span>
                <button type="button" class="btn-close btn-close-white ms-1" style="font-size:.55rem"
                        onclick="LocationPicker.remove(this)"></button>
            </span>
        @endforeach
        <span id="no-locations-hint" class="{{ count($existingLocations) ? 'd-none' : '' }} text-muted small align-self-center">
            No locations selected — defaults to India
        </span>
    </div>

    {{-- Tab + Search card --}}
    <div class="card border">
        <div class="card-header p-0">
            <ul class="nav nav-tabs card-header-tabs border-0" id="locTypeTabs" role="tablist">
                @foreach(['country' => ['Country','ti-globe'], 'region' => ['State / Region','ti-map-2'], 'city' => ['City','ti-building-skyscraper'], 'zip' => ['Pincode / Zip','ti-mailbox']] as $tab => [$label, $icon])
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $tab === 'country' ? 'active' : '' }} px-3 py-2"
                                id="tab-{{ $tab }}" data-tab="{{ $tab }}"
                                onclick="LocationPicker.setTab('{{ $tab }}')" type="button">
                            <i class="ti {{ $icon }} me-1"></i>{{ $label }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body pb-2 pt-2">
            <div class="position-relative">
                <input type="text" id="loc_search_input" class="form-control form-control-sm"
                       placeholder="Type to search countries…" autocomplete="off">
                <div id="loc_suggestions"
                     class="position-absolute w-100 bg-white border rounded shadow-sm d-none"
                     style="top:calc(100% + 4px); max-height:220px; overflow-y:auto; z-index:1055;">
                </div>
            </div>
            <div id="loc_country_filter" class="d-none mt-2">
                <small class="text-muted">Filtering by country:
                    <strong id="loc_country_filter_name"></strong>
                    <a href="#" onclick="LocationPicker.clearCountryFilter(); return false;" class="ms-1 text-danger small">× clear</a>
                </small>
            </div>
        </div>
    </div>
    <div class="form-text mt-1">
        <strong>Country</strong> — adds entire country &nbsp;|&nbsp;
        <strong>State</strong> — targets a state/region &nbsp;|&nbsp;
        <strong>City</strong> — targets a specific city &nbsp;|&nbsp;
        <strong>Pincode</strong> — targets an area by postal code
    </div>
</div>

<style>
.location-tag { font-size: .8rem; line-height:1.4; }
.loc-item { cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
.loc-item:last-child { border-bottom: none; }
.loc-item:hover { background: #f8f9fa; }
.loc-item .loc-type-badge { font-size: .65rem; }
</style>

<script>
window.LocationPicker = (function () {
    const searchUrl = '{{ route('marketplace.vendor.meta-ads.ad-sets.search-locations') }}';
    const hidden    = document.getElementById('targeting_locations_input');
    const tagsWrap  = document.getElementById('location-tags');
    const input     = document.getElementById('loc_search_input');
    const dropdown  = document.getElementById('loc_suggestions');
    const noHint    = document.getElementById('no-locations-hint');

    const tabPlaceholders = {
        country: 'Type to search countries…',
        region:  'Type to search states / regions…',
        city:    'Type to search cities…',
        zip:     'Type a pincode / zip code…',
    };
    const typeColors = { country:'primary', region:'info', city:'success', zip:'secondary' };

    let locations = @json($existingLocations);
    let activeTab = 'country';
    let countryCode = null;
    let countryName = null;
    let debounce;

    function syncHidden() {
        hidden.value = JSON.stringify(locations);
        noHint.classList.toggle('d-none', locations.length > 0);
    }

    function renderTags() {
        // Remove existing tags (keep no-hint span)
        tagsWrap.querySelectorAll('.location-tag').forEach(el => el.remove());

        locations.forEach((loc, idx) => {
            const isStr = typeof loc === 'string';
            const type  = isStr ? 'country' : (loc.type || 'country');
            const label = isStr ? loc.toUpperCase()
                : (loc.name || '')
                  + (loc.region       ? ', ' + loc.region       : '')
                  + (loc.country_name ? ', ' + loc.country_name : '');
            const color = typeColors[type] || 'primary';

            const span = document.createElement('span');
            span.className = `badge bg-${color} d-flex align-items-center gap-1 px-2 py-2 location-tag`;
            span.style.fontSize = '.8rem';
            span.innerHTML = `<i class="ti ti-map-pin me-1" style="font-size:.75rem"></i>${esc(label)}`
                + `<span class="ms-1 opacity-75" style="font-size:.65rem">(${cap(type)})</span>`
                + `<button type="button" class="btn-close btn-close-white ms-1" style="font-size:.55rem" data-idx="${idx}"></button>`;
            span.querySelector('button').addEventListener('click', () => removeIdx(idx));
            tagsWrap.insertBefore(span, noHint);
        });

        noHint.classList.toggle('d-none', locations.length > 0);
    }

    function removeIdx(idx) {
        locations.splice(idx, 1);
        syncHidden();
        renderTags();
    }

    function remove(btn) {
        const tag = btn.closest('.location-tag');
        const locData = tag.dataset.loc ? JSON.parse(tag.dataset.loc) : null;
        if (locData) {
            locations = locations.filter(l => JSON.stringify(l) !== JSON.stringify(locData));
            syncHidden();
            renderTags();
        }
    }

    function isDuplicate(loc) {
        return locations.some(l =>
            (typeof l === 'object' && typeof loc === 'object' && l.key === loc.key && l.type === loc.type)
        );
    }

    function addLocation(loc) {
        if (isDuplicate(loc)) { showDropdown([{_msg:'Already added'}]); return; }
        locations.push(loc);
        syncHidden();
        renderTags();
        input.value = '';
        dropdown.classList.add('d-none');

        // After adding a country, offer to filter states/cities by it
        if (loc.type === 'country') {
            countryCode = loc.key;
            countryName = loc.name;
        }
    }

    function setTab(tab) {
        activeTab = tab;
        document.querySelectorAll('#locTypeTabs .nav-link').forEach(el => {
            el.classList.toggle('active', el.dataset.tab === tab);
        });
        input.placeholder = tabPlaceholders[tab] || 'Search…';
        input.value = '';
        dropdown.classList.add('d-none');
        // Show country filter hint for region/city/zip if a country was selected
        updateCountryFilterHint();
    }

    function updateCountryFilterHint() {
        const filterDiv  = document.getElementById('loc_country_filter');
        const filterName = document.getElementById('loc_country_filter_name');
        if (countryCode && activeTab !== 'country') {
            filterDiv.classList.remove('d-none');
            filterName.textContent = countryName || countryCode;
        } else {
            filterDiv.classList.add('d-none');
        }
    }

    function clearCountryFilter() {
        countryCode = null;
        countryName = null;
        updateCountryFilterHint();
    }

    function showDropdown(items) {
        dropdown.innerHTML = '';
        if (!items.length) {
            dropdown.innerHTML = '<div class="px-3 py-2 text-muted small">No results found.</div>';
            dropdown.classList.remove('d-none');
            return;
        }
        if (items[0]._msg) {
            dropdown.innerHTML = `<div class="px-3 py-2 text-warning small">${esc(items[0]._msg)}</div>`;
            dropdown.classList.remove('d-none');
            return;
        }
        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'loc-item';
            const color = typeColors[item.type] || 'secondary';
            div.innerHTML = `<div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="fw-semibold">${esc(item.name)}</span>
                    ${item.region       ? `<small class="text-muted ms-1">${esc(item.region)}</small>` : ''}
                    ${item.country_name ? `<small class="text-muted ms-1">${esc(item.country_name)}</small>` : ''}
                </div>
                <span class="badge bg-${color} loc-type-badge">${cap(item.type)}</span>
            </div>`;
            div.addEventListener('mousedown', e => {
                e.preventDefault();
                addLocation({
                    key:          item.key,
                    type:         item.type,
                    name:         item.name,
                    region:       item.region || null,
                    country_name: item.country_name || null,
                    country_code: item.country_code || null,
                });
            });
            dropdown.appendChild(div);
        });
        dropdown.classList.remove('d-none');
    }

    function doSearch(q) {
        let url = `${searchUrl}?q=${encodeURIComponent(q)}&tab=${activeTab}`;
        if (countryCode && activeTab !== 'country') {
            url += `&country_code=${encodeURIComponent(countryCode)}`;
        }
        fetch(url)
            .then(r => r.json())
            .then(data => showDropdown(Array.isArray(data) ? data : []))
            .catch(() => dropdown.classList.add('d-none'));
    }

    input.addEventListener('input', function () {
        clearTimeout(debounce);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.classList.add('d-none'); return; }
        debounce = setTimeout(() => doSearch(q), 300);
    });

    input.addEventListener('blur', () => setTimeout(() => dropdown.classList.add('d-none'), 200));
    input.addEventListener('focus', function () {
        if (this.value.trim().length >= 2) doSearch(this.value.trim());
    });

    function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    return { setTab, remove, clearCountryFilter };
})();
</script>
