@extends('layouts.app')
@section('title', 'Geography Manager — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'geography'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Geography Manager</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="toast toast-error">{{ session('error') }}</div>@endif

            {{-- Tab Navigation --}}
            <div class="geo-tabs">
                <button class="geo-tab active" onclick="switchTab('countries')">&#127758; Countries ({{ $countries->count() }})</button>
                <button class="geo-tab" onclick="switchTab('regions')">&#128205; Regions ({{ $regions->count() }})</button>
                <button class="geo-tab" onclick="switchTab('access')">&#128274; Company Access</button>
            </div>

            {{-- ═══ COUNTRIES TAB ═══ --}}
            <div class="geo-panel" id="tab-countries">
                <div class="page-header">
                    <h2>Countries</h2>
                    <button class="btn btn-primary" onclick="document.getElementById('countryModal').classList.add('open')">+ Add Country</button>
                </div>
                <div class="card">
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Flag</th><th>Name</th><th>Code</th><th>Continent</th><th>Regions</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @forelse($countries as $c)
                                <tr>
                                    <td style="font-size:20px;">{{ $c->code ? emoji_flag($c->code) : '🏳' }}</td>
                                    <td style="font-weight:600;">{{ $c->name }}</td>
                                    <td><span class="badge badge-blue">{{ $c->code }}</span></td>
                                    <td>{{ $c->continent }}</td>
                                    <td>{{ $c->regions_count }}</td>
                                    <td>
                                        <form method="POST" action="{{ url('/geography/countries/' . $c->id . '/toggle') }}" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="badge {{ $c->is_active ? 'badge-green' : 'badge-red' }}" style="border:none;cursor:pointer;">
                                                {{ $c->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm" style="background:#eef2ff;color:#4338ca;" onclick="editCountry({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ $c->code }}', '{{ $c->continent }}')">Edit</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7"><div class="empty-state"><p>No countries</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══ REGIONS TAB ═══ --}}
            <div class="geo-panel" id="tab-regions" style="display:none;">
                <div class="page-header">
                    <h2>Regions</h2>
                    <button class="btn btn-primary" onclick="document.getElementById('regionModal').classList.add('open')">+ Add Region</button>
                </div>

                {{-- Filter --}}
                <div style="margin-bottom:16px;display:flex;gap:12px;align-items:center;">
                    <select id="regionFilter" onchange="filterRegions()" style="padding:8px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;">
                        <option value="">All countries</option>
                        @foreach($countries as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="regionSearch" oninput="filterRegions()" placeholder="Search regions…" style="padding:8px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;width:240px;">
                </div>

                <div class="card">
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Name</th><th>Country</th><th>Type</th><th>Status</th><th></th></tr></thead>
                            <tbody id="regionsBody">
                                @forelse($regions as $r)
                                <tr data-country="{{ $r->country_id }}" data-name="{{ strtolower($r->name) }}">
                                    <td style="font-weight:600;">{{ $r->name }}</td>
                                    <td>{{ $r->country?->name ?? '—' }}</td>
                                    <td><span class="badge badge-purple">{{ ucfirst($r->type) }}</span></td>
                                    <td>
                                        <form method="POST" action="{{ url('/geography/regions/' . $r->id . '/toggle') }}" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="badge {{ $r->is_active ? 'badge-green' : 'badge-red' }}" style="border:none;cursor:pointer;">
                                                {{ $r->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm" style="background:#eef2ff;color:#4338ca;" onclick="editRegion({{ $r->id }}, '{{ addslashes($r->name) }}', {{ $r->country_id }}, '{{ $r->type }}')">Edit</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-state"><p>No regions</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══ COMPANY ACCESS TAB ═══ --}}
            <div class="geo-panel" id="tab-access" style="display:none;">
                <div class="page-header">
                    <h2>Company Country Access</h2>
                    <p style="color:#6b7280;font-size:14px;">Control which countries each company can use in destinations, hotels, and itineraries.</p>
                </div>

                <div class="access-grid">
                    @foreach($companies as $company)
                    <div class="card" style="margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <div>
                                <h3 style="font-size:16px;font-weight:700;">{{ $company->name }}</h3>
                                <span style="font-size:12px;color:#6b7280;">
                                    {{ $company->countries->count() }} of {{ $countries->where('is_active', true)->count() }} countries licensed
                                </span>
                            </div>
                            <div style="display:flex;gap:6px;">
                                <button class="btn btn-sm" style="background:#f0fdf4;color:#166534;font-size:11px;" onclick="selectAll({{ $company->id }})">All</button>
                                <button class="btn btn-sm" style="background:#fef2f2;color:#991b1b;font-size:11px;" onclick="selectNone({{ $company->id }})">None</button>
                            </div>
                        </div>
                        <form method="POST" action="{{ url('/geography/companies/' . $company->id . '/access') }}">
                            @csrf @method('PUT')
                            <div class="country-chips">
                                @foreach($countries->where('is_active', true) as $c)
                                <label class="country-chip {{ $company->countries->contains($c->id) ? 'selected' : '' }}">
                                    <input type="checkbox" name="country_ids[]" value="{{ $c->id }}"
                                           {{ $company->countries->contains($c->id) ? 'checked' : '' }}
                                           onchange="this.parentElement.classList.toggle('selected')">
                                    <span>{{ emoji_flag($c->code) }} {{ $c->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            <div style="margin-top:12px;text-align:right;">
                                <button type="submit" class="btn btn-primary btn-sm">Save Access</button>
                            </div>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ ADD COUNTRY MODAL ═══ --}}
<div class="modal-backdrop" id="countryModal">
    <div class="modal">
        <h3>Add Country</h3>
        <form method="POST" action="{{ url('/geography/countries') }}">
            @csrf
            <div class="form-group">
                <label>Country Name *</label>
                <input type="text" name="name" required placeholder="e.g. Tanzania">
            </div>
            <div class="form-group">
                <label>ISO Code *</label>
                <input type="text" name="code" required placeholder="e.g. TZ" maxlength="5" style="text-transform:uppercase;">
            </div>
            <div class="form-group">
                <label>Continent *</label>
                <select name="continent" required>
                    <option value="Africa" selected>Africa</option>
                    <option value="Asia">Asia</option>
                    <option value="Europe">Europe</option>
                    <option value="North America">North America</option>
                    <option value="South America">South America</option>
                    <option value="Oceania">Oceania</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('countryModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Country</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ EDIT COUNTRY MODAL ═══ --}}
<div class="modal-backdrop" id="editCountryModal">
    <div class="modal">
        <h3>Edit Country</h3>
        <form method="POST" id="editCountryForm">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Country Name *</label>
                <input type="text" name="name" id="ecName" required>
            </div>
            <div class="form-group">
                <label>ISO Code *</label>
                <input type="text" name="code" id="ecCode" required maxlength="5" style="text-transform:uppercase;">
            </div>
            <div class="form-group">
                <label>Continent *</label>
                <select name="continent" id="ecContinent" required>
                    <option value="Africa">Africa</option>
                    <option value="Asia">Asia</option>
                    <option value="Europe">Europe</option>
                    <option value="North America">North America</option>
                    <option value="South America">South America</option>
                    <option value="Oceania">Oceania</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('editCountryModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Country</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ ADD REGION MODAL ═══ --}}
<div class="modal-backdrop" id="regionModal">
    <div class="modal">
        <h3>Add Region</h3>
        <form method="POST" action="{{ url('/geography/regions') }}">
            @csrf
            <div class="form-group">
                <label>Country *</label>
                <select name="country_id" required>
                    @foreach($countries->where('is_active', true) as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Region Name *</label>
                <input type="text" name="name" required placeholder="e.g. Northern Circuit">
            </div>
            <div class="form-group">
                <label>Type *</label>
                <select name="type" required>
                    <option value="region">Region</option>
                    <option value="circuit">Circuit</option>
                    <option value="zone">Zone</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('regionModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Region</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ EDIT REGION MODAL ═══ --}}
<div class="modal-backdrop" id="editRegionModal">
    <div class="modal">
        <h3>Edit Region</h3>
        <form method="POST" id="editRegionForm">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Country *</label>
                <select name="country_id" id="erCountry" required>
                    @foreach($countries->where('is_active', true) as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Region Name *</label>
                <input type="text" name="name" id="erName" required>
            </div>
            <div class="form-group">
                <label>Type *</label>
                <select name="type" id="erType" required>
                    <option value="region">Region</option>
                    <option value="circuit">Circuit</option>
                    <option value="zone">Zone</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('editRegionModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Region</button>
            </div>
        </form>
    </div>
</div>

<style>
    .geo-tabs { display: flex; gap: 4px; margin-bottom: 24px; background: #f3f4f6; padding: 4px; border-radius: 10px; }
    .geo-tab { flex: 1; padding: 10px 16px; border: none; background: none; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; border-radius: 8px; transition: all 0.2s; }
    .geo-tab.active { background: #fff; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.08); font-weight: 600; }
    .geo-tab:hover:not(.active) { color: #374151; }
    .country-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .country-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 2px solid #e5e7eb; border-radius: 20px; font-size: 13px; cursor: pointer; transition: all 0.15s; user-select: none; }
    .country-chip input { display: none; }
    .country-chip.selected { border-color: #4f46e5; background: #eef2ff; color: #4338ca; font-weight: 600; }
    .country-chip:hover { border-color: #a5b4fc; }
</style>

<script>
function switchTab(tab) {
    document.querySelectorAll('.geo-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.geo-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = 'block';
    event.target.classList.add('active');
}

function editCountry(id, name, code, continent) {
    document.getElementById('editCountryForm').action = '/Itinex/public/geography/countries/' + id;
    document.getElementById('ecName').value = name;
    document.getElementById('ecCode').value = code;
    document.getElementById('ecContinent').value = continent;
    document.getElementById('editCountryModal').classList.add('open');
}

function editRegion(id, name, countryId, type) {
    document.getElementById('editRegionForm').action = '/Itinex/public/geography/regions/' + id;
    document.getElementById('erName').value = name;
    document.getElementById('erCountry').value = countryId;
    document.getElementById('erType').value = type;
    document.getElementById('editRegionModal').classList.add('open');
}

function filterRegions() {
    const countryId = document.getElementById('regionFilter').value;
    const search = document.getElementById('regionSearch').value.toLowerCase();
    document.querySelectorAll('#regionsBody tr').forEach(row => {
        const matchCountry = !countryId || row.dataset.country === countryId;
        const matchSearch = !search || row.dataset.name.includes(search);
        row.style.display = matchCountry && matchSearch ? '' : 'none';
    });
}

function selectAll(companyId) {
    const form = event.target.closest('.card').querySelector('form');
    form.querySelectorAll('input[type="checkbox"]').forEach(cb => { cb.checked = true; cb.parentElement.classList.add('selected'); });
}

function selectNone(companyId) {
    const form = event.target.closest('.card').querySelector('form');
    form.querySelectorAll('input[type="checkbox"]').forEach(cb => { cb.checked = false; cb.parentElement.classList.remove('selected'); });
}

// Close modals on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
</script>

@php
function emoji_flag($code) {
    if (!$code || strlen($code) < 2) return '🏳';
    $code = strtoupper(substr($code, 0, 2));
    return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
}
@endphp
@endsection
