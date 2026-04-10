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
    <div id="location-tags" class="d-flex flex-wrap gap-2 mb-2" style="min-height:28px">
        @foreach($existingLocations as $loc)
            @php
                $isStr = is_string($loc);
                $type  = $isStr ? 'country' : ($loc['type'] ?? 'country');
                $label = $isStr ? strtoupper($loc) : (
                    ($loc['name'] ?? '') .
                    (!empty($loc['region'])       ? ', ' . $loc['region']       : '') .
                    (!empty($loc['country_name']) ? ', ' . $loc['country_name'] : '')
                );
                $bgMap = ['country' => '#0d6efd', 'region' => '#0dcaf0', 'city' => '#198754', 'zip' => '#6c757d'];
                $bg    = $bgMap[$type] ?? '#0d6efd';
            @endphp
            <span class="location-tag d-inline-flex align-items-center gap-1 px-2 py-1 rounded text-white"
                  style="font-size:.8rem; background:{{ $bg }};"
                  data-loc="{{ json_encode($isStr ? ['key' => $loc, 'type' => 'country', 'name' => strtoupper($loc)] : $loc) }}">
                {{ $label }}
                <span class="opacity-75" style="font-size:.65rem">({{ ucfirst($type) }})</span>
                <button type="button" onclick="LocationPicker.remove(this)"
                        style="background:none;border:none;color:#fff;font-size:.75rem;line-height:1;padding:0 2px;cursor:pointer;"
                        title="Remove">×</button>
            </span>
        @endforeach
        <span id="no-locations-hint" class="{{ count($existingLocations) ? 'd-none' : '' }} text-muted small align-self-center fst-italic">
            No locations selected — defaults to India
        </span>
    </div>

    {{-- Tab + Search box --}}
    <div style="border:1px solid #dee2e6; border-radius:8px; overflow:visible; background:#fff;">

        {{-- Tab bar --}}
        <div style="display:flex; border-bottom:1px solid #dee2e6; background:#f8f9fa; border-radius:8px 8px 0 0; overflow:hidden;">
            @foreach(['country' => ['🌍','Country'], 'region' => ['🗺️','State'], 'city' => ['🏙️','City'], 'zip' => ['📮','Pincode']] as $tab => [$icon, $lbl])
                <button type="button" id="loctab-{{ $tab }}" data-tab="{{ $tab }}"
                        onclick="LocationPicker.setTab('{{ $tab }}')"
                        style="flex:1; border:none; border-right:1px solid #dee2e6; padding:10px 4px;
                               font-size:.82rem; font-weight:600; cursor:pointer; transition:background .15s;
                               background:{{ $tab === 'country' ? '#fff' : '#f8f9fa' }};
                               color:{{ $tab === 'country' ? '#0d6efd' : '#555' }};
                               border-bottom:{{ $tab === 'country' ? '2px solid #0d6efd' : '2px solid transparent' }};">
                    {{ $icon }} {{ $lbl }}
                </button>
            @endforeach
        </div>

        {{-- Search area --}}
        <div style="padding:12px; position:relative;">
            <input type="text" id="loc_search_input" autocomplete="off"
                   placeholder="Type to search countries…"
                   style="width:100%; padding:8px 12px; border:1px solid #ced4da; border-radius:6px;
                          font-size:.9rem; outline:none; box-sizing:border-box;">
            <div id="loc_suggestions"
                 style="display:none; position:absolute; left:12px; right:12px; top:calc(100% - 0px);
                        background:#fff; border:1px solid #ced4da; border-radius:6px;
                        box-shadow:0 4px 12px rgba(0,0,0,.12); max-height:220px; overflow-y:auto; z-index:9999;">
            </div>
        </div>

        {{-- Country-scope hint for sub-tabs --}}
        <div id="loc_country_filter" style="display:none; padding:0 12px 10px; font-size:.8rem; color:#555;">
            Filtering by: <strong id="loc_filter_name"></strong>
            <a href="#" onclick="LocationPicker.clearCountryFilter();return false;"
               style="color:#dc3545; margin-left:6px;">× clear</a>
        </div>
    </div>

    <div style="font-size:.78rem; color:#888; margin-top:6px;">
        <span style="color:#0d6efd">●</span> Country &nbsp;
        <span style="color:#0dcaf0">●</span> State &nbsp;
        <span style="color:#198754">●</span> City &nbsp;
        <span style="color:#6c757d">●</span> Pincode
        &nbsp;·&nbsp; Type 2+ characters to search. Select a country first for better state/city/pincode results.
    </div>
</div>

<script>
window.LocationPicker = (function () {
    const searchUrl = '{{ route('marketplace.vendor.meta-ads.ad-sets.search-locations') }}';
    const hidden    = document.getElementById('targeting_locations_input');
    const tagsWrap  = document.getElementById('location-tags');
    const input     = document.getElementById('loc_search_input');
    const dropdown  = document.getElementById('loc_suggestions');
    const noHint    = document.getElementById('no-locations-hint');

    const placeholders = {
        country: 'Type to search countries…',
        region:  'Type to search states / regions…',
        city:    'Type to search cities…',
        zip:     'Type a pincode or zip code…',
    };
    const bgColors = { country:'#0d6efd', region:'#0dcaf0', city:'#198754', zip:'#6c757d' };
    const textColors = { country:'#fff', region:'#000', city:'#fff', zip:'#fff' };

    let locations  = @json($existingLocations);
    let activeTab  = 'country';
    let cCode      = null;
    let cName      = null;
    let debounce;

    function syncHidden() {
        hidden.value = JSON.stringify(locations);
        noHint.classList.toggle('d-none', locations.length > 0);
        if (!locations.length) noHint.classList.remove('d-none');
    }

    function renderTags() {
        tagsWrap.querySelectorAll('.location-tag').forEach(el => el.remove());
        locations.forEach((loc, idx) => {
            const isStr = typeof loc === 'string';
            const type  = isStr ? 'country' : (loc.type || 'country');
            const label = isStr ? loc.toUpperCase()
                : (loc.name || '')
                  + (loc.region       ? ', ' + loc.region       : '')
                  + (loc.country_name ? ', ' + loc.country_name : '');
            const bg   = bgColors[type]   || '#0d6efd';
            const tc   = textColors[type] || '#fff';
            const span = document.createElement('span');
            span.className = 'location-tag d-inline-flex align-items-center gap-1 px-2 py-1 rounded';
            span.style.cssText = `font-size:.8rem;background:${bg};color:${tc};`;
            span.innerHTML = `${esc(label)}<span style="font-size:.65rem;opacity:.75">(${cap(type)})</span>`
                + `<button type="button" data-idx="${idx}"
                      style="background:none;border:none;color:${tc};font-size:.85rem;line-height:1;padding:0 2px;cursor:pointer;">×</button>`;
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

    // Called from inline onclick on server-rendered tags
    function remove(btn) {
        const tag = btn.closest('.location-tag');
        if (!tag) return;
        const raw = tag.dataset.loc;
        if (!raw) return;
        const loc = JSON.parse(raw);
        locations = locations.filter(l => JSON.stringify(l) !== JSON.stringify(loc));
        syncHidden();
        renderTags();
    }

    function isDuplicate(loc) {
        return locations.some(l =>
            typeof l === 'object' && typeof loc === 'object' && l.key === loc.key && l.type === loc.type
        );
    }

    function addLocation(loc) {
        if (isDuplicate(loc)) {
            showMsg('Already added.'); return;
        }
        locations.push(loc);
        if (loc.type === 'country') { cCode = loc.key; cName = loc.name; }
        syncHidden();
        renderTags();
        input.value = '';
        dropdown.style.display = 'none';
        updateFilterHint();
    }

    function setTab(tab) {
        activeTab = tab;
        document.querySelectorAll('[id^="loctab-"]').forEach(btn => {
            const active = btn.dataset.tab === tab;
            btn.style.background        = active ? '#fff'      : '#f8f9fa';
            btn.style.color             = active ? '#0d6efd'   : '#555';
            btn.style.borderBottom      = active ? '2px solid #0d6efd' : '2px solid transparent';
        });
        input.placeholder = placeholders[tab] || 'Search…';
        input.value = '';
        dropdown.style.display = 'none';
        input.focus();
        updateFilterHint();
    }

    function updateFilterHint() {
        const div  = document.getElementById('loc_country_filter');
        const name = document.getElementById('loc_filter_name');
        if (cCode && activeTab !== 'country') {
            div.style.display = 'block';
            name.textContent  = cName || cCode;
        } else {
            div.style.display = 'none';
        }
    }

    function clearCountryFilter() {
        cCode = null; cName = null;
        updateFilterHint();
    }

    function showMsg(msg) {
        dropdown.innerHTML = `<div style="padding:10px 14px;color:#888;font-size:.85rem;">${esc(msg)}</div>`;
        dropdown.style.display = 'block';
    }

    function showDropdown(items) {
        dropdown.innerHTML = '';
        if (!items.length) { showMsg('No results found.'); return; }
        items.forEach(item => {
            const bg = bgColors[item.type] || '#6c757d';
            const tc = textColors[item.type] || '#fff';
            const div = document.createElement('div');
            div.style.cssText = 'padding:9px 14px;cursor:pointer;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;';
            div.innerHTML = `<div>
                <span style="font-weight:600">${esc(item.name)}</span>
                ${item.region       ? `<span style="color:#888;font-size:.82rem;margin-left:5px">${esc(item.region)}</span>`       : ''}
                ${item.country_name ? `<span style="color:#888;font-size:.82rem;margin-left:5px">${esc(item.country_name)}</span>` : ''}
            </div>
            <span style="font-size:.7rem;font-weight:600;background:${bg};color:${tc};padding:2px 7px;border-radius:20px;">${cap(item.type)}</span>`;
            div.addEventListener('mouseenter', () => div.style.background = '#f8f9fa');
            div.addEventListener('mouseleave', () => div.style.background = '#fff');
            div.addEventListener('mousedown', e => {
                e.preventDefault();
                addLocation({ key: item.key, type: item.type, name: item.name,
                               region: item.region || null, country_name: item.country_name || null,
                               country_code: item.country_code || null });
            });
            dropdown.appendChild(div);
        });
        dropdown.style.display = 'block';
    }

    function doSearch(q) {
        let url = `${searchUrl}?q=${encodeURIComponent(q)}&tab=${activeTab}`;
        if (cCode && activeTab !== 'country') url += `&country_code=${encodeURIComponent(cCode)}`;

        showMsg('Searching…');

        fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => {
                if (!r.ok) {
                    return r.text().then(txt => { throw new Error(`HTTP ${r.status}: ${txt.substring(0, 120)}`); });
                }
                return r.json();
            })
            .then(data => {
                if (Array.isArray(data)) {
                    showDropdown(data);
                } else if (data && data.error) {
                    showMsg('⚠ ' + data.error);
                } else {
                    showDropdown([]);
                }
            })
            .catch(err => showMsg('⚠ Could not load results: ' + err.message));
    }

    input.addEventListener('input', function () {
        clearTimeout(debounce);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }
        debounce = setTimeout(() => doSearch(q), 300);
    });
    input.addEventListener('blur',  () => setTimeout(() => dropdown.style.display = 'none', 200));
    input.addEventListener('focus', function () { if (this.value.trim().length >= 2) doSearch(this.value.trim()); });

    function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    return { setTab, remove, clearCountryFilter };
})();
</script>
