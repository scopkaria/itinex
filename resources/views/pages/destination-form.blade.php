@extends('layouts.app')
@section('title', ($mode === 'edit' ? 'Edit ' . $destination->name : 'New Destination') . ' — Itinex')

@section('styles')
<style>
    .field-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
    .field-grid .form-group { margin: 0; }
    .rate-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); margin-bottom: 12px; overflow: hidden; transition: all var(--transition-fast); }
    .rate-card:hover { border-color: #cbd5e1; }
    .rate-card-header { padding: 12px 18px; background: var(--bg-table-head); display: flex; align-items: center; justify-content: space-between; cursor: pointer; gap: 12px; }
    .rate-card-header .rc-title { font-size: 13px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .rate-card-body { padding: 20px; display: none; border-top: 1px solid var(--border-light); }
    .rate-card.open .rate-card-body { display: block; }
    .rate-card-header .rc-arrow { transition: transform var(--transition-normal); font-size: 11px; color: var(--text-muted); flex-shrink: 0; }
    .rate-card.open .rc-arrow { transform: rotate(180deg); }
    .rate-group { background: var(--bg-table-head); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 24px; margin-top: 20px; }
    .rate-group-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .rate-field-row { display: grid; gap: 12px; margin-bottom: 14px; }
    .rate-field-row.cols-2 { grid-template-columns: repeat(2, 1fr); }
    .rate-field-row.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .rate-field-row.cols-4 { grid-template-columns: repeat(4, 1fr); }
    .rate-field-row .rf { display: flex; flex-direction: column; }
    .rate-field-row .rf label { font-size: 11px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; }
    .rate-field-row .rf input, .rate-field-row .rf select { padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; transition: all var(--transition-fast); }
    .rate-field-row .rf input:focus, .rate-field-row .rf select:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-ring); }
    .vat-radios { display: flex; gap: 16px; padding-top: 4px; }
    .vat-radios label { font-size: 12px; display: flex; align-items: center; gap: 5px; cursor: pointer; color: var(--text-secondary); }
    .badge-ft { font-size: 10px; padding: 2px 10px; border-radius: 10px; font-weight: 600; background: #dbeafe; color: #1e40af; }
    .badge-vat { font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
    .vat-inclusive { background: var(--color-success-light); color: #166534; }
    .vat-exclusive { background: var(--color-warning-light); color: #92400e; }
    .vat-exempted { background: #f1f5f9; color: #475569; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px; transition: color var(--transition-fast); }
    .back-link:hover { color: var(--color-primary); }
    .clone-year-bar { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: var(--color-info-light); border: 1px solid #bae6fd; border-radius: var(--radius-sm); margin-bottom: 16px; }
    .clone-year-bar label { font-size: 12px; font-weight: 600; color: #0c4a6e; white-space: nowrap; }
    .clone-year-bar select, .clone-year-bar button { padding: 6px 14px; border-radius: var(--radius-sm); font-size: 12px; }
    .clone-year-bar select { border: 1px solid #7dd3fc; }
    .clone-year-bar button { background: #0284c7; color: #fff; border: none; font-weight: 600; cursor: pointer; transition: all var(--transition-fast); }
    .clone-year-bar button:hover { background: #0369a1; }
    .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding-bottom: 4px; }
    .section-label-indigo { color: var(--color-primary); }
    .section-label-green { color: var(--color-success); }
    .section-label-amber { color: var(--color-warning); }
    .section-label-gray { color: var(--text-muted); }
    .search-select { position: relative; }
    .search-select input[type="text"] { width: 100%; padding: 9px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; }
    .search-select .ss-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card); border: 1px solid var(--border-color); border-top: none; border-radius: 0 0 var(--radius-sm) var(--radius-sm); max-height: 220px; overflow-y: auto; z-index: 999; display: none; box-shadow: var(--shadow-lg); }
    .search-select.open .ss-dropdown { display: block; }
    .search-select .ss-item { padding: 9px 14px; font-size: 13px; cursor: pointer; transition: background var(--transition-fast); }
    .search-select .ss-item:hover { background: var(--bg-table-head); }
    .search-select .ss-item.active { background: var(--color-primary-light); color: var(--color-primary); font-weight: 600; }
    .quick-add-row { display: flex; gap: 6px; margin-top: 6px; }
    .quick-add-row input { flex: 1; padding: 7px 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 12px; }
    .quick-add-row button { padding: 7px 14px; background: var(--color-success); color: #fff; border: none; border-radius: var(--radius-sm); font-size: 11px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .quick-add-row button:hover { background: #047857; }
    .gallery-upload-zone { border: 2px dashed var(--border-color); border-radius: var(--radius-lg); padding: 36px 20px; text-align: center; cursor: pointer; transition: all var(--transition-fast); background: var(--bg-table-head); }
    .gallery-upload-zone:hover, .gallery-upload-zone.dragover { border-color: var(--color-primary); background: var(--color-primary-light); }
    .gallery-upload-zone .upload-icon { font-size: 36px; color: var(--text-muted); margin-bottom: 8px; }
    .gallery-upload-zone p { font-size: 13px; color: var(--text-secondary); margin: 0; }
    .gallery-upload-zone .upload-hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 14px; margin-top: 16px; }
    .gallery-item { position: relative; border-radius: var(--radius-md); overflow: hidden; border: 2px solid var(--border-color); background: var(--bg-table-head); transition: all var(--transition-fast); cursor: grab; }
    .gallery-item:hover { border-color: var(--color-primary); box-shadow: 0 4px 12px rgba(79,70,229,.12); }
    .gallery-item.is-cover { border-color: var(--color-success); box-shadow: 0 0 0 2px var(--color-success); }
    .gallery-item.dragging { opacity: .5; }
    .gallery-item img { width: 100%; height: 140px; object-fit: cover; display: block; }
    .gallery-item-actions { position: absolute; top: 6px; right: 6px; display: flex; gap: 4px; }
    .gallery-item-actions button { width: 28px; height: 28px; border-radius: var(--radius-sm); border: none; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; transition: all var(--transition-fast); }
    .gi-cover { background: rgba(255,255,255,.9); color: var(--text-muted); }
    .gi-cover:hover, .gi-cover.active { background: var(--color-success); color: #fff; }
    .gi-delete { background: rgba(255,255,255,.9); color: var(--text-muted); }
    .gi-delete:hover { background: #ef4444; color: #fff; }
    .gallery-item .cover-badge { position: absolute; bottom: 0; left: 0; right: 0; background: var(--color-success); color: #fff; font-size: 10px; font-weight: 700; text-align: center; padding: 3px 0; text-transform: uppercase; letter-spacing: .5px; }
    .gallery-progress { margin-top: 12px; display: none; }
    .gallery-progress-bar { height: 4px; background: var(--border-color); border-radius: 2px; overflow: hidden; }
    .gallery-progress-fill { height: 100%; background: var(--color-primary); border-radius: 2px; transition: width .3s; width: 0; }
    .gallery-count { font-size: 12px; color: var(--text-muted); }
    @media (max-width: 640px) {
        .rate-field-row.cols-3, .rate-field-row.cols-4 { grid-template-columns: 1fr 1fr; }
        .field-grid { grid-template-columns: 1fr; }
        .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
    }
</style>
@endsection

@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'destinations'])
    <div class="main-content">
        <header class="topbar">
            <h2>{{ $mode === 'edit' ? 'Edit Destination' : 'New Destination' }}</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if($errors->any())
                <div style="background:#fef2f2;color:#991b1b;padding:12px 16px;border-radius:var(--radius-sm);font-size:13px;margin-bottom:16px;border:1px solid #fecaca;">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <a href="{{ url('/destinations') }}" class="back-link">&larr; Back to Destinations</a>

            {{-- ─── SECTION 1: Basic Info ── --}}
            <div class="form-section" id="section1">
                <div class="form-section-header" onclick="toggleSection('section1')">
                    <span class="sec-num">1</span>
                    <h3>Basic Information</h3>
                    <span class="sec-arrow">&#9660;</span>
                </div>
                <div class="form-section-body">
                    <form method="POST" action="{{ $mode === 'edit' ? url('/destinations/' . $destination->id) : url('/destinations') }}" id="destForm">
                        @csrf
                        @if($mode === 'edit') @method('PUT') @endif
                        @if($mode === 'create') @include('partials.company-selector') @endif
                        <div class="field-grid">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" value="{{ old('name', $destination->name ?? '') }}" required placeholder="e.g. Serengeti National Park">
                            </div>
                            <div class="form-group">
                                <label>Category *</label>
                                <select name="category" required>
                                    @foreach(['national_park' => 'National Park', 'conservancy' => 'Conservancy', 'reserve' => 'Reserve', 'marine_park' => 'Marine Park', 'other' => 'Other'] as $k => $v)
                                    <option value="{{ $k }}" {{ old('category', $destination->category ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Supplier</label>
                                <input type="text" name="supplier" value="{{ old('supplier', $destination->supplier ?? '') }}" placeholder="e.g. TANAPA">
                            </div>
                            <div class="form-group">
                                <label>Country *</label>
                                <div class="search-select" id="countrySelect">
                                    <input type="text" id="countrySearch" autocomplete="off" placeholder="Search country…"
                                           value="{{ old('country_id') ? $countries->firstWhere('id', old('country_id'))?->name : ($destination?->countryRef?->name ?? '') }}">
                                    <input type="hidden" name="country_id" id="countryIdInput"
                                           value="{{ old('country_id', $destination->country_id ?? '') }}">
                                    <div class="ss-dropdown" id="countryDropdown">
                                        @foreach($countries as $c)
                                        <div class="ss-item" data-id="{{ $c->id }}" data-name="{{ $c->name }}"
                                             data-regions='@json($c->regions->map(fn($r) => ["id" => $r->id, "name" => $r->name]))'>
                                            {{ $c->name }}
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Region</label>
                                <select name="region_id" id="regionSelect">
                                    <option value="">— Select region —</option>
                                </select>
                                <div class="quick-add-row" id="quickAddRegion" style="display:none;">
                                    <input type="text" id="newRegionName" placeholder="New region name">
                                    <button type="button" onclick="quickAddRegion()">+ Add</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email', $destination->email ?? '') }}" placeholder="contact@supplier.com">
                            </div>
                        </div>
                        <div style="margin-top:20px;display:flex;justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary">{{ $mode === 'edit' ? 'Update Destination' : 'Create Destination' }}</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($mode === 'edit')
            {{-- ─── SECTION 2: Pricing / Rates ── --}}
            <div class="form-section" id="section2">
                <div class="form-section-header" onclick="toggleSection('section2')">
                    <span class="sec-num">2</span>
                    <h3>Pricing &amp; Rates</h3>
                    <span style="font-size:12px;color:var(--text-muted);">{{ $rates->count() }} {{ Str::plural('rate', $rates->count()) }}</span>
                    <span class="sec-arrow">&#9660;</span>
                </div>
                <div class="form-section-body">
                    @if($rates->count())
                    <div class="clone-year-bar">
                        <label>&#128197; Clone all rates forward:</label>
                        <form method="POST" action="{{ url('/destinations/' . $destination->id . '/clone-rates') }}" style="display:flex;gap:8px;align-items:center;">
                            @csrf
                            <select name="year_offset">
                                <option value="1">+1 year</option>
                                <option value="2">+2 years</option>
                                <option value="3">+3 years</option>
                            </select>
                            <button type="submit" onclick="return confirm('Clone {{ $rates->count() }} rates forward?')">Clone Rates</button>
                        </form>
                    </div>
                    @foreach($rates as $r)
                    <div class="rate-card" id="rateCard{{ $r->id }}">
                        <div class="rate-card-header" onclick="document.getElementById('rateCard{{ $r->id }}').classList.toggle('open')">
                            <div class="rc-title">
                                <span class="badge-ft">{{ $r->fee_type }}</span>
                                <span class="badge badge-green">{{ $r->season_name }}</span>
                                <span style="color:var(--text-muted);font-size:12px;">
                                    {{ $r->valid_from?->format('M Y') ?? '—' }} → {{ $r->valid_to?->format('M Y') ?? '—' }}
                                </span>
                                <span style="font-weight:600;font-size:12px;">NR: ${{ number_format($r->nr_adult, 2) }}</span>
                                <span class="badge-vat vat-{{ $r->vat_type }}">{{ ucfirst($r->vat_type) }}</span>
                                @if($r->markup > 0)<span style="color:var(--color-warning);font-size:11px;">+{{ $r->markup }}%</span>@endif
                            </div>
                            <span class="rc-arrow">&#9660;</span>
                        </div>
                        <div class="rate-card-body">
                            <form method="POST" action="{{ url('/destinations/' . $destination->id . '/fees/' . $r->id) }}">
                                @csrf @method('PUT')
                                <div class="rate-field-row cols-4">
                                    <div class="rf"><label>Fee Type *</label>
                                        <select name="fee_type" required>
                                            @foreach(['Park Fee','Conservation Fee','Conservancy Fee','Crater Service Fee','Transit Fee','WMA Fee','Concession Fee','Wildlife Fee','Permit','Walking Fee','Ranger Fee','Night Park Fee','Canoe Fee','Village Fee','Other'] as $ft)
                                            <option value="{{ $ft }}" {{ $r->fee_type === $ft ? 'selected' : '' }}>{{ $ft }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="rf"><label>Season *</label>
                                        <select name="season_name" required>
                                            @foreach(['Year Round','High Season','Low Season','Peak Season','Green Season','All Year','July - June','Jan - Jun','Jul - Dec','June - Nov'] as $s)
                                            <option value="{{ $s }}" {{ $r->season_name === $s ? 'selected' : '' }}>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="rf"><label>Valid From</label><input type="date" name="valid_from" value="{{ $r->valid_from?->format('Y-m-d') }}"></div>
                                    <div class="rf"><label>Valid To</label><input type="date" name="valid_to" value="{{ $r->valid_to?->format('Y-m-d') }}"></div>
                                </div>
                                <div class="section-label section-label-indigo">Non-Resident</div>
                                <div class="rate-field-row cols-2">
                                    <div class="rf"><label>Adult ($)</label><input type="number" name="nr_adult" step="0.01" min="0" value="{{ $r->nr_adult }}" required></div>
                                    <div class="rf"><label>Child ($)</label><input type="number" name="nr_child" step="0.01" min="0" value="{{ $r->nr_child }}" required></div>
                                </div>
                                <div class="section-label section-label-green">Resident</div>
                                <div class="rate-field-row cols-2">
                                    <div class="rf"><label>Adult ($)</label><input type="number" name="resident_adult" step="0.01" min="0" value="{{ $r->resident_adult }}" required></div>
                                    <div class="rf"><label>Child ($)</label><input type="number" name="resident_child" step="0.01" min="0" value="{{ $r->resident_child }}" required></div>
                                </div>
                                <div class="section-label section-label-amber">Citizen</div>
                                <div class="rate-field-row cols-2">
                                    <div class="rf"><label>Adult ($)</label><input type="number" name="citizen_adult" step="0.01" min="0" value="{{ $r->citizen_adult }}" required></div>
                                    <div class="rf"><label>Child ($)</label><input type="number" name="citizen_child" step="0.01" min="0" value="{{ $r->citizen_child }}" required></div>
                                </div>
                                <div class="section-label section-label-gray">Additional</div>
                                <div class="rate-field-row cols-2">
                                    <div class="rf"><label>Vehicle ($)</label><input type="number" name="vehicle_rate" step="0.01" min="0" value="{{ $r->vehicle_rate }}" required></div>
                                    <div class="rf"><label>Guide ($)</label><input type="number" name="guide_rate" step="0.01" min="0" value="{{ $r->guide_rate }}" required></div>
                                </div>
                                <div class="rate-field-row cols-2" style="align-items:start;">
                                    <div class="rf">
                                        <label>VAT Type *</label>
                                        <div class="vat-radios">
                                            <label><input type="radio" name="vat_type" value="inclusive" {{ $r->vat_type === 'inclusive' ? 'checked' : '' }}> Inclusive</label>
                                            <label><input type="radio" name="vat_type" value="exclusive" {{ $r->vat_type === 'exclusive' ? 'checked' : '' }}> Exclusive</label>
                                            <label><input type="radio" name="vat_type" value="exempted" {{ $r->vat_type === 'exempted' ? 'checked' : '' }}> Exempted</label>
                                        </div>
                                    </div>
                                    <div class="rf"><label>Markup (%)</label><input type="number" name="markup" step="0.01" min="0" max="500" value="{{ $r->markup }}" required></div>
                                </div>
                                <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center;">
                                    <button type="submit" class="btn btn-primary btn-sm">Update Rate</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ url('/destinations/' . $destination->id . '/fees/' . $r->id) }}" onsubmit="return confirm('Remove this rate?')" style="text-align:right;margin-top:8px;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">&#128465; Remove</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">No rates yet — add one below.</p>
                    @endif

                    <div class="rate-group">
                        <div class="rate-group-title">&#10133; Add New Rate</div>
                        <form method="POST" action="{{ url('/destinations/' . $destination->id . '/fees') }}">
                            @csrf
                            <div class="rate-field-row cols-4">
                                <div class="rf"><label>Fee Type *</label>
                                    <select name="fee_type" required>
                                        @foreach(['Park Fee','Conservation Fee','Conservancy Fee','Crater Service Fee','Transit Fee','WMA Fee','Concession Fee','Wildlife Fee','Permit','Walking Fee','Ranger Fee','Night Park Fee','Canoe Fee','Village Fee','Other'] as $ft)
                                        <option value="{{ $ft }}">{{ $ft }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="rf"><label>Season *</label>
                                    <select name="season_name" required>
                                        @foreach(['Year Round','High Season','Low Season','Peak Season','Green Season','All Year','July - June'] as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="rf"><label>Valid From</label><input type="date" name="valid_from"></div>
                                <div class="rf"><label>Valid To</label><input type="date" name="valid_to"></div>
                            </div>
                            <div class="section-label section-label-indigo">Non-Resident Rates</div>
                            <div class="rate-field-row cols-2">
                                <div class="rf"><label>Adult ($)</label><input type="number" name="nr_adult" step="0.01" min="0" value="0" required></div>
                                <div class="rf"><label>Child ($)</label><input type="number" name="nr_child" step="0.01" min="0" value="0" required></div>
                            </div>
                            <div class="section-label section-label-green">Resident Rates</div>
                            <div class="rate-field-row cols-2">
                                <div class="rf"><label>Adult ($)</label><input type="number" name="resident_adult" step="0.01" min="0" value="0" required></div>
                                <div class="rf"><label>Child ($)</label><input type="number" name="resident_child" step="0.01" min="0" value="0" required></div>
                            </div>
                            <div class="section-label section-label-amber">Citizen Rates</div>
                            <div class="rate-field-row cols-2">
                                <div class="rf"><label>Adult ($)</label><input type="number" name="citizen_adult" step="0.01" min="0" value="0" required></div>
                                <div class="rf"><label>Child ($)</label><input type="number" name="citizen_child" step="0.01" min="0" value="0" required></div>
                            </div>
                            <div class="section-label section-label-gray">Additional Fees</div>
                            <div class="rate-field-row cols-2">
                                <div class="rf"><label>Vehicle ($)</label><input type="number" name="vehicle_rate" step="0.01" min="0" value="0" required></div>
                                <div class="rf"><label>Guide ($)</label><input type="number" name="guide_rate" step="0.01" min="0" value="0" required></div>
                            </div>
                            <div class="rate-field-row cols-2" style="align-items:start;">
                                <div class="rf">
                                    <label>VAT Type *</label>
                                    <div class="vat-radios">
                                        <label><input type="radio" name="vat_type" value="inclusive" checked> Inclusive</label>
                                        <label><input type="radio" name="vat_type" value="exclusive"> Exclusive</label>
                                        <label><input type="radio" name="vat_type" value="exempted"> Exempted</label>
                                    </div>
                                </div>
                                <div class="rf"><label>Markup (%)</label><input type="number" name="markup" step="0.01" min="0" max="500" value="0" required></div>
                            </div>
                            <div style="margin-top:16px;display:flex;justify-content:flex-end;">
                                <button type="submit" class="btn btn-primary">Save Rate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ─── SECTION 3: Gallery ── --}}
            <div class="form-section" id="section3">
                <div class="form-section-header" onclick="toggleSection('section3')">
                    <span class="sec-num">3</span>
                    <h3>Gallery</h3>
                    <span class="gallery-count" id="galleryCount">{{ isset($galleryImages) ? $galleryImages->count() : 0 }} image(s)</span>
                    <span class="sec-arrow">&#9660;</span>
                </div>
                <div class="form-section-body">
                    <div class="gallery-upload-zone" id="uploadZone" onclick="document.getElementById('galleryInput').click()">
                        <div class="upload-icon">&#128247;</div>
                        <p>Click or drag images here to upload</p>
                        <p class="upload-hint">JPEG, PNG, WebP — max 5 MB each — up to 20 images</p>
                    </div>
                    <input type="file" id="galleryInput" multiple accept="image/jpeg,image/png,image/webp" style="display:none;">
                    <div class="gallery-progress" id="uploadProgress">
                        <div class="gallery-progress-bar"><div class="gallery-progress-fill" id="uploadProgressFill"></div></div>
                        <p style="font-size:11px;color:var(--text-muted);margin-top:4px;" id="uploadStatusText">Uploading...</p>
                    </div>
                    <div class="gallery-grid" id="galleryGrid">
                        @if(isset($galleryImages))
                        @foreach($galleryImages as $img)
                        <div class="gallery-item {{ $img->is_cover ? 'is-cover' : '' }}" draggable="true" data-id="{{ $img->id }}">
                            <img src="{{ asset('storage/' . $img->file_path) }}" alt="" loading="lazy">
                            <div class="gallery-item-actions">
                                <button type="button" class="gi-cover {{ $img->is_cover ? 'active' : '' }}" onclick="setCover({{ $destination->id }}, {{ $img->id }})" title="Set as cover">&#9733;</button>
                                <button type="button" class="gi-delete" onclick="deleteMedia({{ $destination->id }}, {{ $img->id }})" title="Delete">&#10005;</button>
                            </div>
                            @if($img->is_cover)<div class="cover-badge">Cover</div>@endif
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ── Searchable Country Dropdown ──
const countrySearch = document.getElementById('countrySearch');
const countryIdInput = document.getElementById('countryIdInput');
const countryDropdown = document.getElementById('countryDropdown');
const countrySelectEl = document.getElementById('countrySelect');
const regionSelect = document.getElementById('regionSelect');
const quickAddRegionEl = document.getElementById('quickAddRegion');
const countryItems = countryDropdown.querySelectorAll('.ss-item');
let currentRegions = [];
const initialCountryId = countryIdInput.value;
const initialRegionId = '{{ old("region_id", $destination->region_id ?? "") }}';

if (initialCountryId) {
    const initialItem = countryDropdown.querySelector('[data-id="' + initialCountryId + '"]');
    if (initialItem) { currentRegions = JSON.parse(initialItem.dataset.regions); populateRegions(currentRegions, initialRegionId); }
}
countrySearch.addEventListener('focus', () => { countrySelectEl.classList.add('open'); filterCountries(''); });
countrySearch.addEventListener('input', function() { countrySelectEl.classList.add('open'); filterCountries(this.value); });
document.addEventListener('click', e => { if (!countrySelectEl.contains(e.target)) countrySelectEl.classList.remove('open'); });

function filterCountries(q) {
    const lower = q.toLowerCase();
    countryItems.forEach(item => { item.style.display = item.dataset.name.toLowerCase().includes(lower) ? '' : 'none'; });
}
countryItems.forEach(item => {
    item.addEventListener('click', function() {
        countrySearch.value = this.dataset.name;
        countryIdInput.value = this.dataset.id;
        countrySelectEl.classList.remove('open');
        countryItems.forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        currentRegions = JSON.parse(this.dataset.regions);
        populateRegions(currentRegions, '');
    });
});
function populateRegions(regions, selectedId) {
    regionSelect.innerHTML = '<option value="">— Select region —</option>';
    regions.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.id; opt.textContent = r.name;
        if (String(r.id) === String(selectedId)) opt.selected = true;
        regionSelect.appendChild(opt);
    });
    quickAddRegionEl.style.display = countryIdInput.value ? 'flex' : 'none';
}
function quickAddRegion() {
    const name = document.getElementById('newRegionName').value.trim();
    if (!name || !countryIdInput.value) return;
    fetch('{{ url("/api/countries") }}/' + countryIdInput.value + '/regions', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ name })
    }).then(r => r.json()).then(region => {
        currentRegions.push({ id: region.id, name: region.name });
        const item = countryDropdown.querySelector('[data-id="' + countryIdInput.value + '"]');
        if (item) item.dataset.regions = JSON.stringify(currentRegions);
        populateRegions(currentRegions, region.id);
        document.getElementById('newRegionName').value = '';
    });
}

@if($mode === 'edit')
// ── Gallery ──
const uploadZone = document.getElementById('uploadZone');
const galleryInput = document.getElementById('galleryInput');
const galleryGrid = document.getElementById('galleryGrid');
const uploadProgress = document.getElementById('uploadProgress');
const uploadProgressFill = document.getElementById('uploadProgressFill');
const uploadStatusText = document.getElementById('uploadStatusText');
const galleryCount = document.getElementById('galleryCount');
const destinationId = {{ $destination->id }};
const csrfToken = '{{ csrf_token() }}';

uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => { e.preventDefault(); uploadZone.classList.remove('dragover'); if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files); });
galleryInput.addEventListener('change', function() { if (this.files.length) uploadFiles(this.files); this.value = ''; });

function uploadFiles(files) {
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) formData.append('images[]', files[i]);
    uploadProgress.style.display = 'block'; uploadProgressFill.style.width = '0%';
    uploadStatusText.textContent = 'Uploading ' + files.length + ' image(s)...';
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/destinations/' + destinationId + '/media');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken); xhr.setRequestHeader('Accept', 'application/json');
    xhr.upload.addEventListener('progress', e => { if (e.lengthComputable) uploadProgressFill.style.width = Math.round((e.loaded / e.total) * 100) + '%'; });
    xhr.onload = function() {
        uploadProgress.style.display = 'none';
        if (xhr.status === 200) { JSON.parse(xhr.responseText).media.forEach(m => addGalleryItem(m)); updateGalleryCount(); }
        else { let msg = 'Upload failed.'; try { const err = JSON.parse(xhr.responseText); msg = err.message || Object.values(err.errors).flat().join(', '); } catch(e) {} alert(msg); }
    };
    xhr.onerror = () => { uploadProgress.style.display = 'none'; alert('Upload failed.'); };
    xhr.send(formData);
}
function addGalleryItem(media) {
    const div = document.createElement('div');
    div.className = 'gallery-item' + (media.is_cover ? ' is-cover' : '');
    div.draggable = true; div.dataset.id = media.id;
    div.innerHTML = '<img src="/storage/' + media.file_path + '" alt="" loading="lazy"><div class="gallery-item-actions"><button type="button" class="gi-cover' + (media.is_cover ? ' active' : '') + '" onclick="setCover(' + destinationId + ',' + media.id + ')">&#9733;</button><button type="button" class="gi-delete" onclick="deleteMedia(' + destinationId + ',' + media.id + ')">&#10005;</button></div>' + (media.is_cover ? '<div class="cover-badge">Cover</div>' : '');
    galleryGrid.appendChild(div); initDragItem(div);
}
function setCover(destId, mediaId) {
    fetch('/destinations/' + destId + '/media/' + mediaId + '/cover', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } }).then(r => r.json()).then(() => {
        galleryGrid.querySelectorAll('.gallery-item').forEach(el => { el.classList.remove('is-cover'); el.querySelector('.gi-cover').classList.remove('active'); const b = el.querySelector('.cover-badge'); if (b) b.remove(); });
        const item = galleryGrid.querySelector('[data-id="' + mediaId + '"]');
        if (item) { item.classList.add('is-cover'); item.querySelector('.gi-cover').classList.add('active'); const b = document.createElement('div'); b.className = 'cover-badge'; b.textContent = 'Cover'; item.appendChild(b); }
    });
}
function deleteMedia(destId, mediaId) {
    if (!confirm('Delete this image?')) return;
    fetch('/destinations/' + destId + '/media/' + mediaId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } }).then(r => r.json()).then(() => {
        const item = galleryGrid.querySelector('[data-id="' + mediaId + '"]'); if (item) item.remove(); updateGalleryCount();
        const first = galleryGrid.querySelector('.gallery-item');
        if (first && !galleryGrid.querySelector('.is-cover')) { first.classList.add('is-cover'); first.querySelector('.gi-cover').classList.add('active'); const b = document.createElement('div'); b.className = 'cover-badge'; b.textContent = 'Cover'; first.appendChild(b); }
    });
}
function updateGalleryCount() { galleryCount.textContent = galleryGrid.querySelectorAll('.gallery-item').length + ' image(s)'; }

let dragSrcEl = null;
function initDragItem(item) {
    item.addEventListener('dragstart', function(e) { if (e.target.tagName === 'BUTTON') { e.preventDefault(); return; } dragSrcEl = this; this.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', this.dataset.id); });
    item.addEventListener('dragover', e => { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
    item.addEventListener('dragenter', function() { this.style.borderColor = 'var(--color-primary)'; });
    item.addEventListener('dragleave', function() { this.style.borderColor = ''; });
    item.addEventListener('drop', function(e) { e.stopPropagation(); e.preventDefault(); this.style.borderColor = ''; if (dragSrcEl !== this) { const p = this.parentNode; const all = [...p.querySelectorAll('.gallery-item')]; if (all.indexOf(dragSrcEl) < all.indexOf(this)) p.insertBefore(dragSrcEl, this.nextSibling); else p.insertBefore(dragSrcEl, this); saveReorder(); } });
    item.addEventListener('dragend', function() { this.classList.remove('dragging'); galleryGrid.querySelectorAll('.gallery-item').forEach(el => el.style.borderColor = ''); });
}
function saveReorder() {
    const order = [...galleryGrid.querySelectorAll('.gallery-item')].map(el => parseInt(el.dataset.id));
    fetch('/destinations/' + destinationId + '/media/reorder', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify({ order }) });
}
galleryGrid.querySelectorAll('.gallery-item').forEach(initDragItem);
@endif
</script>
@endsection