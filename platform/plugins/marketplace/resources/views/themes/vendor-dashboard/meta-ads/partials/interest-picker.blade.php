{{--
  Interest Picker — searches Meta's real interest index (type=adinterest) so only
  ID-backed interests Meta actually recognizes get submitted (free text was
  previously collected but silently dropped before reaching Meta).
  Props: $existingInterests (array of {id, name} objects)
--}}
@php $existingInterests = $existingInterests ?? []; @endphp

<div class="mb-3">
    <label class="form-label">Interests</label>

    <input type="hidden" name="targeting_interests" id="targeting_interests_input"
           value="{{ json_encode($existingInterests) }}">

    <div id="interest-tags" class="d-flex flex-wrap gap-2 mb-2" style="min-height:28px">
        @foreach($existingInterests as $interest)
            <span class="interest-tag d-inline-flex align-items-center gap-1 px-2 py-1 rounded text-white"
                  style="font-size:.8rem; background:#6f42c1;"
                  data-interest="{{ json_encode($interest) }}">
                {{ $interest['name'] ?? '' }}
                <button type="button" onclick="InterestPicker.remove(this)"
                        style="background:none;border:none;color:#fff;font-size:.75rem;line-height:1;padding:0 2px;cursor:pointer;"
                        title="Remove">×</button>
            </span>
        @endforeach
        <span id="no-interests-hint" class="{{ count($existingInterests) ? 'd-none' : '' }} text-muted small align-self-center fst-italic">
            No interests selected — Meta will target broadly
        </span>
    </div>

    <div style="position:relative;">
        <input type="text" id="interest_search_input" autocomplete="off"
               placeholder="Type to search interests, e.g. Fashion, Fitness…"
               class="form-control">
        <div id="interest_suggestions"
             style="display:none; position:absolute; left:0; right:0; top:100%;
                    background:#fff; border:1px solid #ced4da; border-radius:6px;
                    box-shadow:0 4px 12px rgba(0,0,0,.12); max-height:220px; overflow-y:auto; z-index:9999;">
        </div>
    </div>
    <div class="form-text">Search and pick from Meta's real interest categories. Leave empty to target broadly.</div>
</div>

<script>
window.InterestPicker = (function () {
    const searchUrl = '{{ route('marketplace.vendor.meta-ads.ad-sets.search-interests') }}';
    const hidden    = document.getElementById('targeting_interests_input');
    const tagsWrap  = document.getElementById('interest-tags');
    const input     = document.getElementById('interest_search_input');
    const dropdown  = document.getElementById('interest_suggestions');
    const noHint    = document.getElementById('no-interests-hint');

    let interests = @json($existingInterests);
    let debounce;

    function syncHidden() {
        hidden.value = JSON.stringify(interests);
        noHint.classList.toggle('d-none', interests.length > 0);
    }

    function renderTags() {
        tagsWrap.querySelectorAll('.interest-tag').forEach(el => el.remove());
        interests.forEach((it, idx) => {
            const span = document.createElement('span');
            span.className = 'interest-tag d-inline-flex align-items-center gap-1 px-2 py-1 rounded text-white';
            span.style.cssText = 'font-size:.8rem;background:#6f42c1;';
            span.innerHTML = `${esc(it.name)}<button type="button"
                  style="background:none;border:none;color:#fff;font-size:.85rem;line-height:1;padding:0 2px;cursor:pointer;">×</button>`;
            span.querySelector('button').addEventListener('click', () => removeIdx(idx));
            tagsWrap.insertBefore(span, noHint);
        });
        noHint.classList.toggle('d-none', interests.length > 0);
    }

    function removeIdx(idx) {
        interests.splice(idx, 1);
        syncHidden();
        renderTags();
    }

    function remove(btn) {
        const tag = btn.closest('.interest-tag');
        if (!tag) return;
        const raw = tag.dataset.interest;
        if (!raw) return;
        const it = JSON.parse(raw);
        interests = interests.filter(i => i.id !== it.id);
        syncHidden();
        renderTags();
    }

    function isDuplicate(it) {
        return interests.some(i => i.id === it.id);
    }

    function addInterest(it) {
        if (isDuplicate(it)) { showMsg('Already added.'); return; }
        interests.push(it);
        syncHidden();
        renderTags();
        input.value = '';
        dropdown.style.display = 'none';
    }

    function showMsg(msg) {
        dropdown.innerHTML = `<div style="padding:10px 14px;color:#888;font-size:.85rem;">${esc(msg)}</div>`;
        dropdown.style.display = 'block';
    }

    function showDropdown(items) {
        dropdown.innerHTML = '';
        if (!items.length) { showMsg('No results found.'); return; }
        items.forEach(item => {
            const div = document.createElement('div');
            div.style.cssText = 'padding:9px 14px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
            div.innerHTML = `<span style="font-weight:600">${esc(item.name)}</span>`
                + (item.audience_size_lower_bound ? `<span style="color:#888;font-size:.78rem;margin-left:6px">~${Number(item.audience_size_lower_bound).toLocaleString()}+ people</span>` : '');
            div.addEventListener('mouseenter', () => div.style.background = '#f8f9fa');
            div.addEventListener('mouseleave', () => div.style.background = '#fff');
            div.addEventListener('mousedown', e => {
                e.preventDefault();
                addInterest({ id: item.id, name: item.name });
            });
            dropdown.appendChild(div);
        });
        dropdown.style.display = 'block';
    }

    function doSearch(q) {
        showMsg('Searching…');
        fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
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

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    return { remove };
})();
</script>
