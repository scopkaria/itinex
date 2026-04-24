@extends('layouts.app')
@section('title', ($hotel->name ?? 'New') . ' — Accommodation — Itinex')
@section('styles')
<style>
    .detail-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
    .detail-header h2 { font-size:22px; font-weight:700; }
    .detail-header .back-link { font-size:13px; color:var(--text-secondary); text-decoration:none; }
    .detail-header .back-link:hover { color:var(--color-primary); }
    .tab-container { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden; }
    .tab-body { padding:24px; }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .info-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
    @media(max-width:768px) { .info-grid, .info-grid-3 { grid-template-columns:1fr; } }
    .mini-table { width:100%; border-collapse:collapse; font-size:13px; }
    .mini-table th { text-align:left; padding:8px 12px; background:var(--bg-table-head); border-bottom:1px solid var(--border-color); font-weight:600; color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
    .mini-table td { padding:8px 12px; border-bottom:1px solid var(--border-light); }
    .mini-table tr:last-child td { border-bottom:none; }
    .inline-form { display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; padding:16px; background:var(--bg-table-head); border-radius:var(--radius-sm); margin-bottom:16px; }
    .inline-form .form-group { margin-bottom:0; flex:1; min-width:120px; }
    .inline-form .form-group label { font-size:11px; font-weight:600; color:var(--text-secondary); margin-bottom:4px; display:block; }
    .inline-form .form-group input, .inline-form .form-group select { font-size:12px; padding:6px 10px; }
    .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:12px; }
    .gallery-item { position:relative; border-radius:var(--radius-sm); overflow:hidden; aspect-ratio:4/3; background:#f1f5f9; }
    .gallery-item img { width:100%; height:100%; object-fit:cover; }
    .gallery-item .overlay { position:absolute; inset:0; background:rgba(0,0,0,.4); display:flex; align-items:center; justify-content:center; gap:8px; opacity:0; transition:opacity var(--transition-fast); }
    .gallery-item:hover .overlay { opacity:1; }
    .gallery-item .overlay button, .gallery-item .overlay a { padding:4px 10px; font-size:11px; border-radius:4px; border:none; cursor:pointer; font-weight:600; }
    .cover-badge { position:absolute; top:6px; left:6px; background:var(--color-primary); color:#fff; font-size:10px; padding:2px 8px; border-radius:10px; font-weight:700; }
    .year-btn { padding:6px 16px; font-size:12px; font-weight:600; border:1px solid var(--border-color); background:var(--bg-card); border-radius:var(--radius-sm); cursor:pointer; color:var(--text-secondary); }
    .year-btn.active { background:var(--color-success); color:#fff; border-color:var(--color-success); }
    .section-title { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border-light); }
    .cat-tag { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; }
    .cat-budget { background:var(--color-warning-light); color:#92400e; }
    .cat-midrange { background:var(--color-info-light); color:#1e40af; }
    .cat-luxury { background:#fce7f3; color:#9d174d; }
</style>
@endsection
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'accommodations'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Accommodation</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="detail-header">
                <div>
                    <a href="{{ url('/accommodations') }}" class="back-link">&larr; Back to Accommodation</a>
                    <h2>{{ $hotel->name }}</h2>
                </div>
                <span class="cat-tag cat-{{ $hotel->category }}">{{ $hotel->category }}</span>
            </div>

            <div class="tab-container" id="mainTabs">
                <div class="tab-nav">
                    <button class="active" data-tab="tab-overview">Overview</button>
                    <button data-tab="tab-gallery">Gallery</button>
                    <button data-tab="tab-description">Description</button>
                    <button data-tab="tab-room-cat">Room Categories</button>
                    <button data-tab="tab-room-types">Room Types</button>
                    <button data-tab="tab-meal-plans">Meal Plans</button>
                    <button data-tab="tab-rates">Rates &amp; Policies</button>
                </div>
                <div class="tab-body">

{{-- ═══ Tab 1: Overview ═══ --}}
<div class="tab-content active" id="tab-overview">
    @if(!$canManageAccommodation)
        <div class="toast" style="margin-bottom:14px;background:#eff6ff;color:#1e3a8a;">Read-only mode: only Super Admin and assigned Accommodation Owner can modify this property.</div>
    @endif

    <form method="POST" action="{{ url('/accommodations/' . $hotel->id) }}">
        @csrf @method('PUT')

        <div class="section-title">General Details</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ $hotel->name }}" required {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
            <div class="form-group">
                <label>Destination *</label>
                <select name="location_id" required {{ !$canManageAccommodation ? 'disabled' : '' }}>
                    <option value="">Select</option>
                    @foreach($destinations as $d)<option value="{{ $d->id }}" {{ $hotel->location_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required {{ !$canManageAccommodation ? 'disabled' : '' }}>
                    @foreach(['budget','midrange','luxury'] as $c)<option value="{{ $c }}" {{ $hotel->category == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Chain</label><input type="text" name="chain" value="{{ $hotel->chain }}" {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
        </div>

        <div class="section-title">Contacts</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" value="{{ $hotel->contact_person }}" {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ $hotel->phone }}" {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ $hotel->email }}" {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
            <div class="form-group"><label>Website</label><input type="url" name="website" value="{{ $hotel->website }}" {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
        </div>

        <div class="section-title">Location</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Address</label><textarea name="address" rows="2" {{ !$canManageAccommodation ? 'disabled' : '' }}>{{ $hotel->address }}</textarea></div>
            <div class="info-grid">
                <div class="form-group"><label>Latitude</label><input type="number" name="latitude" step="0.0000001" value="{{ $hotel->latitude }}" disabled></div>
                <div class="form-group"><label>Longitude</label><input type="number" name="longitude" step="0.0000001" value="{{ $hotel->longitude }}" disabled></div>
            </div>
        </div>

        <div class="section-title">VAT &amp; Markup</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group">
                <label>VAT Type</label>
                <select name="vat_type" {{ !$canManageAccommodation ? 'disabled' : '' }}>
                    @foreach(['inclusive','exclusive','exempt'] as $v)<option value="{{ $v }}" {{ $hotel->vat_type == $v ? 'selected' : '' }}>{{ ucfirst($v) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Markup %</label><input type="number" name="markup" step="0.01" value="{{ $hotel->markup }}" {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active" {{ !$canManageAccommodation ? 'disabled' : '' }}>
                    <option value="1" {{ $hotel->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$hotel->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="sticky-save"><button type="submit" class="btn btn-primary" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save Overview</button></div>
    </form>

    @if(auth()->user()->isSuperAdmin())
        <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/owners') }}" style="margin-top:20px;">
            @csrf
            <div class="section-title">Accommodation Owners</div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Assign Hotel Users</label>
                <select name="owner_user_ids[]" multiple size="6">
                    @foreach($ownerCandidates as $candidate)
                        <option value="{{ $candidate->id }}" {{ $hotel->owners->contains('id', $candidate->id) ? 'selected' : '' }}>{{ $candidate->name }} ({{ $candidate->email }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Save Owners</button>
        </form>
    @endif
</div>

{{-- ═══ Tab 2: Gallery ═══ --}}
<div class="tab-content" id="tab-gallery">
    @if(!$canManageAccommodation)
        <div class="empty-state"><p>You can view gallery only.</p></div>
    @endif
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/media') }}" enctype="multipart/form-data" style="margin-bottom:20px;">
        @csrf
        <div style="display:flex;gap:12px;align-items:flex-end;">
            <div class="form-group" style="flex:1;margin-bottom:0;"><label>Upload Images</label><input type="file" name="images[]" multiple accept="image/*" {{ !$canManageAccommodation ? 'disabled' : '' }}></div>
            <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>Upload</button>
        </div>
    </form>
    <div class="gallery-grid">
        @foreach($hotel->accommodationMedia as $m)
        <div class="gallery-item">
            @if($m->is_cover)<div class="cover-badge">Cover</div>@endif
            <img src="{{ asset('storage/' . $m->file_path) }}" alt="">
            <div class="overlay">
                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/media/' . $m->id . '/cover') }}">@csrf @method('PATCH')<button style="background:var(--color-primary);color:#fff;" {{ !$canManageAccommodation ? 'disabled' : '' }}>Cover</button></form>
                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/media/' . $m->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button style="background:#ef4444;color:#fff;" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button></form>
            </div>
        </div>
        @endforeach
    </div>
    @if($hotel->accommodationMedia->isEmpty())
    <div class="empty-state"><div class="empty-icon">&#128247;</div><p>No images yet</p></div>
    @endif
</div>

{{-- ═══ Tab 3: Description ═══ --}}
<div class="tab-content" id="tab-description">
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id) }}">
        @csrf @method('PUT')
        <div class="form-group"><label>Description</label><textarea name="description" rows="12" style="font-size:14px;line-height:1.7;" {{ !$canManageAccommodation ? 'disabled' : '' }}>{{ $hotel->description }}</textarea></div>
        <div class="sticky-save"><button type="submit" class="btn btn-primary" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save Description</button></div>
    </form>
</div>

{{-- ═══ Tab 4: Room Categories ═══ --}}
<div class="tab-content" id="tab-room-cat">
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-categories') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Category Name</label><input type="text" name="name" required placeholder="e.g. Standard"></div>
        <div class="form-group"><label>Description</label><input type="text" name="description" placeholder="Optional"></div>
        <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
    </form>
    @if($hotel->roomCategories->count())
    <table class="mini-table">
        <thead><tr><th>Name</th><th>Description</th><th></th></tr></thead>
        <tbody>
            @foreach($hotel->roomCategories as $rc)
            <tr>
                <td style="font-weight:600;">{{ $rc->name }}</td>
                <td>{{ $rc->description ?? '—' }}</td>
                <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-categories/' . $rc->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No room categories</p></div>
    @endif
</div>

{{-- ═══ Tab 5: Room Types ═══ --}}
<div class="tab-content" id="tab-room-types">
    <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">Choose predefined room types. Max adults auto-fills by type.</p>
    @php
        $roomTypePreset = [
            'single' => ['label' => 'Single', 'max_adults' => 1],
            'double' => ['label' => 'Double', 'max_adults' => 2],
            'twin' => ['label' => 'Twin', 'max_adults' => 2],
            'twin_single' => ['label' => 'Twin + Single', 'max_adults' => 3],
            'triple' => ['label' => 'Triple', 'max_adults' => 3],
            'quadruple' => ['label' => 'Quadruple', 'max_adults' => 4],
            'quintuple' => ['label' => 'Quintuple', 'max_adults' => 5],
            'family' => ['label' => 'Family', 'max_adults' => 6],
        ];
        $selectedRoomTypes = $hotel->roomTypes->pluck('type')->all();
    @endphp

    <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-types/sync') }}" class="card" style="margin-bottom:12px;">
        @csrf
        <div class="info-grid-3">
            @foreach($roomTypePreset as $value => $meta)
                <label style="display:flex;align-items:center;gap:8px;background:var(--bg-muted);padding:10px;border-radius:8px;">
                    <input type="checkbox" name="room_types[]" value="{{ $value }}" {{ in_array($value, $selectedRoomTypes, true) ? 'checked' : '' }} {{ !$canManageAccommodation ? 'disabled' : '' }}>
                    <span>{{ $meta['label'] }} <small style="color:var(--text-muted);">(Max {{ $meta['max_adults'] }})</small></span>
                </label>
            @endforeach
        </div>
        <div class="sticky-save" style="margin-top:12px;"><button type="submit" class="btn btn-primary" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save Room Types</button></div>
    </form>
</div>

{{-- ═══ Tab 6: Meal Plans ═══ --}}
<div class="tab-content" id="tab-meal-plans">
    <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">Manage abbreviated plans with multilingual descriptions.</p>
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/meal-plans') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Abbreviation</label><input type="text" name="abbreviation" required placeholder="AI, FB, HB, BB, GP"></div>
        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required placeholder="All Inclusive"></div>
        <div class="form-group"><label>Description (EN)</label><input type="text" name="description_en"></div>
        <div class="form-group"><label>Description (FR)</label><input type="text" name="description_fr"></div>
        <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
    </form>
    @if($mealPlans->count())
    <table class="mini-table">
        <thead><tr><th>Abbreviation</th><th>Full Name</th><th>Description EN</th></tr></thead>
        <tbody>
            @foreach($mealPlans as $mp)
                <tr>
                    <td>{{ $mp->abbreviation ?? $mp->name }}</td>
                    <td>{{ $mp->full_name ?? $mp->name }}</td>
                    <td>{{ $mp->description_i18n['en'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No meal plans configured</p></div>
    @endif
</div>

{{-- ═══ Tab 7: Rates & Policies ═══ --}}
<div class="tab-content" id="tab-rates">
    <div id="ratesSubTabs">
        <div class="sub-tab-nav">
            <button class="active" data-subtab="sub-years">Rate Years</button>
            <button data-subtab="sub-seasons">Seasons</button>
            <button data-subtab="sub-room-rates">Room Rates</button>
            <button data-subtab="sub-extra-fees">Extra Fees</button>
            <button data-subtab="sub-holiday">Holiday Supplements</button>
            <button data-subtab="sub-activities">Activities</button>
            <button data-subtab="sub-child">Child Policies</button>
            <button data-subtab="sub-payment">Payment Policies</button>
            <button data-subtab="sub-cancel">Cancellation</button>
            <button data-subtab="sub-tour-leader">Tour Leader</button>
            <button data-subtab="sub-backups">Backups</button>
        </div>

        {{-- Sub: Rate Years --}}
        <div data-subtab-content="sub-years" style="display:block;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Year</label><input type="number" name="year" required min="2020" max="2040" value="{{ date('Y') }}"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add Year</button>
            </form>
            @if($hotel->rateYears->count())
            <table class="mini-table">
                <thead><tr><th>Year</th><th>Status</th><th>Seasons</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($hotel->rateYears as $ry)
                    <tr>
                        <td style="font-weight:700;">{{ $ry->year }}</td>
                        <td><span class="badge {{ $ry->is_active ? 'badge-green' : 'badge-gray' }}">{{ $ry->is_active ? 'Active' : 'Draft' }}</span></td>
                        <td>{{ $ry->seasons->count() }}</td>
                        <td>
                            <div style="display:flex;gap:4px;">
                                @if(!$ry->is_active)
                                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years/' . $ry->id . '/activate') }}">@csrf @method('PATCH')<button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Activate</button></form>
                                @endif
                                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years/' . $ry->id . '/clone') }}">@csrf<input type="number" name="target_year" min="2020" max="2050" placeholder="Year" required style="width:90px;"><button class="btn btn-outline btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Clone</button></form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No rate years</p></div>
            @endif
        </div>

        {{-- Sub: Seasons --}}
        <div data-subtab-content="sub-seasons" style="display:none;">
            @if($activeYear)
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Seasons for active year: <strong>{{ $activeYear->year }}</strong></p>
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years/' . $activeYear->id . '/seasons') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. Peak"></div>
                <div class="form-group"><label>Start</label><input type="date" name="start_date" required></div>
                <div class="form-group"><label>End</label><input type="date" name="end_date" required></div>
                <div class="form-group"><label>Location (Optional)</label><select name="location_id"><option value="">All</option>@foreach($destinations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($activeYear->seasons->count())
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Start</th><th>End</th><th></th></tr></thead>
                <tbody>
                    @foreach($activeYear->seasons as $s)
                    <tr>
                        <td style="font-weight:600;">{{ $s->name }}</td>
                        <td>{{ $s->start_date }}</td>
                        <td>{{ $s->end_date }}</td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/seasons/' . $s->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No seasons yet</p></div>
            @endif
            @else
            <div class="empty-state"><p>Create a rate year first and activate it</p></div>
            @endif
        </div>

        {{-- Sub: Room Rates --}}
        <div data-subtab-content="sub-room-rates" style="display:none;">
            @if($activeYear)
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-rates') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Season</label>
                    <select name="season_id" required><option value="">—</option>@foreach($activeYear->seasons as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Room Type</label>
                    <select name="room_type_id" required><option value="">—</option>@foreach($hotel->roomTypes as $rt)<option value="{{ $rt->id }}">{{ $rt->label ?? ucfirst(str_replace('_', ' ', $rt->type)) }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Room Cat</label>
                    <select name="room_category_id"><option value="">—</option>@foreach($hotel->roomCategories as $rc)<option value="{{ $rc->id }}">{{ $rc->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Meal Plan</label>
                    <select name="meal_plan_id"><option value="">—</option>@foreach($mealPlans as $mp)<option value="{{ $mp->id }}">{{ $mp->abbreviation ?? $mp->name }}</option>@endforeach</select>
                </div>
                <input type="hidden" name="rate_year_id" value="{{ $activeYear->id }}">
                <div class="form-group"><label>Rate Kind</label><select name="rate_kind"><option value="sto">STO</option><option value="contract">Contract</option><option value="promo">Promo</option></select></div>
                @if($canViewRawRates)
                    <div class="form-group"><label>STO Raw</label><input type="number" name="sto_rate_raw" step="0.01"></div>
                @endif
                <div class="form-group"><label>Contracted</label><input type="number" name="contracted_rate" step="0.01"></div>
                <div class="form-group"><label>Promotional</label><input type="number" name="promotional_rate" step="0.01"></div>
                <div class="form-group"><label>Markup %</label><input type="number" name="markup_percent" step="0.01" value="0"></div>
                <div class="form-group"><label>Markup Fixed</label><input type="number" name="markup_fixed" step="0.01" value="0"></div>
                <div class="form-group"><label>Fallback Adult</label><input type="number" name="adult_rate" step="0.01"></div>
                <div class="form-group"><label>Child Rate</label><input type="number" name="child_rate" step="0.01" value="0"></div>
                <div class="form-group"><label>Infant Rate</label><input type="number" name="infant_rate" step="0.01" value="0"></div>
                <div class="form-group"><label>Single Supp</label><input type="number" name="single_supplement" step="0.01" value="0"></div>
                <div class="form-group"><label>Sharing Double</label><input type="number" name="per_person_sharing_double" step="0.01" value="0"></div>
                <div class="form-group"><label>Sharing Twin</label><input type="number" name="per_person_sharing_twin" step="0.01" value="0"></div>
                <div class="form-group"><label>Triple Adj.</label><input type="number" name="triple_adjustment" step="0.01" value="0"></div>
                <div class="form-group"><label>Visibility</label><select name="visibility_mode"><option value="private">private</option><option value="computed">computed</option><option value="computed_only">computed_only</option></select></div>
                @if(!empty($canOverrideRates))<div class="form-group"><label>Override</label><select name="is_override"><option value="0">No</option><option value="1">Yes</option></select></div>@endif
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($roomRates->count())
            <table class="mini-table">
                <thead><tr><th>Season</th><th>Room Type</th><th>Meal Plan</th><th>Final</th>@if($canViewRawRates)<th>STO Raw</th>@endif<th>Single</th><th>Double</th><th>Twin</th><th>Triple Adj.</th><th>Visibility</th><th></th></tr></thead>
                <tbody>
                    @foreach($roomRates as $rr)
                    <tr>
                        <td>{{ $rr->season?->name ?? '—' }}</td>
                        <td>{{ $rr->roomType?->label ?? $rr->roomType?->type ?? '—' }}</td>
                        <td>{{ $rr->mealPlan?->abbreviation ?? $rr->mealPlan?->name ?? '—' }}</td>
                        <td style="font-weight:600;">${{ number_format($rr->derived_rate ?? $rr->adult_rate, 2) }}</td>
                        @if($canViewRawRates)<td>{{ $rr->sto_rate_raw !== null ? '$' . number_format((float)$rr->sto_rate_raw, 2) : '—' }}</td>@endif
                        <td>${{ number_format((float) $rr->single_supplement, 2) }}</td>
                        <td>${{ number_format((float) ($rr->per_person_sharing_double ?? 0), 2) }}</td>
                        <td>${{ number_format((float) ($rr->per_person_sharing_twin ?? 0), 2) }}</td>
                        <td>${{ number_format((float) ($rr->triple_adjustment ?? 0), 2) }}</td>
                        <td>{{ strtoupper($rr->visibility_mode ?? 'private') }}</td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-rates/' . $rr->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No room rates yet</p></div>
            @endif
            @else
            <div class="empty-state"><p>Create a rate year first</p></div>
            @endif
        </div>

        {{-- Sub: Extra Fees --}}
        <div data-subtab-content="sub-extra-fees" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/extra-fees') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. Concession"></div>
                <div class="form-group"><label>Fee Type</label>
                    <select name="fee_type" required>
                        <option value="per_person">Per Person</option>
                        <option value="per_room">Per Room</option>
                        <option value="flat">Flat</option>
                    </select>
                </div>
                <div class="form-group"><label>Amount</label><input type="number" name="amount" step="0.01" required></div>
                <div class="form-group"><label>Adult</label><input type="number" name="adult_rate" step="0.01"></div>
                <div class="form-group"><label>Child</label><input type="number" name="child_rate" step="0.01"></div>
                <div class="form-group"><label>Resident</label><input type="number" name="resident_rate" step="0.01"></div>
                <div class="form-group"><label>Non Resident</label><input type="number" name="non_resident_rate" step="0.01"></div>
                <div class="form-group"><label>Apply Per</label>
                    <select name="apply_per">
                        <option value="">Default</option>
                        <option value="person">Person</option>
                        <option value="vehicle">Vehicle</option>
                        <option value="group">Group</option>
                    </select>
                </div>
                <div class="form-group"><label>Valid From</label><input type="date" name="valid_from"></div>
                <div class="form-group"><label>Valid To</label><input type="date" name="valid_to"></div>
                <div class="form-group" style="flex:2;"><label>Description</label><input type="text" name="description"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($hotel->extraFees->count())
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Type</th><th>Amount</th><th>Adult</th><th>Child</th><th>Resident</th><th>Non Resident</th><th>Apply Per</th><th>Validity</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($hotel->extraFees as $fee)
                    <tr>
                        <td colspan="10">
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/extra-fees/' . $fee->id) }}" class="inline-form" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <div class="form-group"><input type="text" name="name" value="{{ $fee->name }}" required></div>
                                <div class="form-group"><select name="fee_type" required><option value="per_person" {{ $fee->fee_type === 'per_person' ? 'selected' : '' }}>Per Person</option><option value="per_room" {{ $fee->fee_type === 'per_room' ? 'selected' : '' }}>Per Room</option><option value="flat" {{ $fee->fee_type === 'flat' ? 'selected' : '' }}>Flat</option></select></div>
                                <div class="form-group"><input type="number" name="amount" step="0.01" value="{{ $fee->amount }}" required></div>
                                <div class="form-group"><input type="number" name="adult_rate" step="0.01" value="{{ $fee->adult_rate }}"></div>
                                <div class="form-group"><input type="number" name="child_rate" step="0.01" value="{{ $fee->child_rate }}"></div>
                                <div class="form-group"><input type="number" name="resident_rate" step="0.01" value="{{ $fee->resident_rate }}"></div>
                                <div class="form-group"><input type="number" name="non_resident_rate" step="0.01" value="{{ $fee->non_resident_rate }}"></div>
                                <div class="form-group"><select name="apply_per"><option value="" {{ empty($fee->apply_per) ? 'selected' : '' }}>Default</option><option value="person" {{ $fee->apply_per === 'person' ? 'selected' : '' }}>Person</option><option value="vehicle" {{ $fee->apply_per === 'vehicle' ? 'selected' : '' }}>Vehicle</option><option value="group" {{ $fee->apply_per === 'group' ? 'selected' : '' }}>Group</option></select></div>
                                <div class="form-group"><input type="date" name="valid_from" value="{{ $fee->valid_from?->format('Y-m-d') }}"></div>
                                <div class="form-group"><input type="date" name="valid_to" value="{{ $fee->valid_to?->format('Y-m-d') }}"></div>
                                <div class="form-group" style="flex:2;"><input type="text" name="description" value="{{ $fee->description }}"></div>
                                <button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
                            </form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/extra-fees/' . $fee->id) }}" style="margin-top:6px;" onsubmit="return confirm('Delete fee?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No extra fees</p></div>
            @endif
        </div>

        {{-- Sub: Holiday Supplements --}}
        <div data-subtab-content="sub-holiday" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/holiday-supplements') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Name</label><input type="text" name="holiday_name" required placeholder="e.g. Christmas"></div>
                <div class="form-group"><label>Start</label><input type="date" name="start_date" required></div>
                <div class="form-group"><label>End</label><input type="date" name="end_date" required></div>
                <div class="form-group"><label>Amount</label><input type="number" name="supplement_amount" step="0.01" required></div>
                <div class="form-group"><label>Adult</label><input type="number" name="adult_rate" step="0.01"></div>
                <div class="form-group"><label>Child</label><input type="number" name="child_rate" step="0.01"></div>
                <div class="form-group"><label>Type</label>
                    <select name="apply_to"><option value="per_person">Per Person</option><option value="per_room">Per Room</option></select>
                </div>
                <div class="form-group"><label>Room Type</label><select name="room_type_id"><option value="">All</option>@foreach($hotel->roomTypes as $rt)<option value="{{ $rt->id }}">{{ $rt->label ?? ucfirst(str_replace('_', ' ', $rt->type)) }}</option>@endforeach</select></div>
                <div class="form-group"><label>Supplement Date</label><input type="date" name="supplement_date"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($hotel->holidaySupplements->count())
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Period</th><th>Amount</th><th>Adult</th><th>Child</th><th>Type</th><th>Room</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($hotel->holidaySupplements as $hs)
                    <tr>
                        <td colspan="9">
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/holiday-supplements/' . $hs->id) }}" class="inline-form" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <div class="form-group"><input type="text" name="holiday_name" value="{{ $hs->holiday_name }}" required></div>
                                <div class="form-group"><input type="date" name="start_date" value="{{ $hs->start_date?->format('Y-m-d') }}" required></div>
                                <div class="form-group"><input type="date" name="end_date" value="{{ $hs->end_date?->format('Y-m-d') }}" required></div>
                                <div class="form-group"><input type="number" name="supplement_amount" step="0.01" value="{{ $hs->supplement_amount }}" required></div>
                                <div class="form-group"><input type="number" name="adult_rate" step="0.01" value="{{ $hs->adult_rate }}"></div>
                                <div class="form-group"><input type="number" name="child_rate" step="0.01" value="{{ $hs->child_rate }}"></div>
                                <div class="form-group"><select name="apply_to" required><option value="per_person" {{ $hs->apply_to === 'per_person' ? 'selected' : '' }}>Per Person</option><option value="per_room" {{ $hs->apply_to === 'per_room' ? 'selected' : '' }}>Per Room</option></select></div>
                                <div class="form-group"><select name="room_type_id"><option value="">All</option>@foreach($hotel->roomTypes as $rt)<option value="{{ $rt->id }}" {{ (int) $hs->room_type_id === (int) $rt->id ? 'selected' : '' }}>{{ $rt->label ?? ucfirst(str_replace('_', ' ', $rt->type)) }}</option>@endforeach</select></div>
                                <div class="form-group"><input type="date" name="supplement_date" value="{{ $hs->supplement_date?->format('Y-m-d') }}"></div>
                                <button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
                            </form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/holiday-supplements/' . $hs->id) }}" style="margin-top:6px;" onsubmit="return confirm('Delete supplement?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No holiday supplements</p></div>
            @endif
        </div>

        {{-- Sub: Activities --}}
        <div data-subtab-content="sub-activities" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/activities') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
                <div class="form-group"><label>Price/Person</label><input type="number" name="price_per_person" step="0.01" required></div>
                <div class="form-group"><label>Adult</label><input type="number" name="rate_adult" step="0.01"></div>
                <div class="form-group"><label>Child</label><input type="number" name="rate_child" step="0.01"></div>
                <div class="form-group"><label>Guide</label><input type="number" name="rate_guide" step="0.01"></div>
                <div class="form-group"><label>Vehicle</label><input type="number" name="rate_vehicle" step="0.01"></div>
                <div class="form-group"><label>Group</label><input type="number" name="rate_group" step="0.01"></div>
                <div class="form-group" style="flex:2;"><label>Description</label><input type="text" name="description"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($hotel->accommodationActivities->count())
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Price</th><th>Adult</th><th>Child</th><th>Guide</th><th>Vehicle</th><th>Group</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($hotel->accommodationActivities as $activity)
                    <tr>
                        <td colspan="9">
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/activities/' . $activity->id) }}" class="inline-form" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <div class="form-group"><input type="text" name="name" value="{{ $activity->name }}" required></div>
                                <div class="form-group"><input type="number" name="price_per_person" step="0.01" value="{{ $activity->price_per_person }}" required></div>
                                <div class="form-group"><input type="number" name="rate_adult" step="0.01" value="{{ $activity->rate_adult }}"></div>
                                <div class="form-group"><input type="number" name="rate_child" step="0.01" value="{{ $activity->rate_child }}"></div>
                                <div class="form-group"><input type="number" name="rate_guide" step="0.01" value="{{ $activity->rate_guide }}"></div>
                                <div class="form-group"><input type="number" name="rate_vehicle" step="0.01" value="{{ $activity->rate_vehicle }}"></div>
                                <div class="form-group"><input type="number" name="rate_group" step="0.01" value="{{ $activity->rate_group }}"></div>
                                <div class="form-group" style="flex:2;"><input type="text" name="description" value="{{ $activity->description }}"></div>
                                <button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
                            </form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/activities/' . $activity->id) }}" style="margin-top:6px;" onsubmit="return confirm('Delete activity?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No activities</p></div>
            @endif
        </div>

        {{-- Sub: Child Policy --}}
        <div data-subtab-content="sub-child" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/child-policies') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Age From</label><input type="number" name="min_age" min="0" max="17" required></div>
                <div class="form-group"><label>Age To</label><input type="number" name="max_age" min="0" max="17" required></div>
                <div class="form-group"><label>Value</label><input type="number" name="value" step="0.01" required></div>
                <div class="form-group"><label>Policy</label>
                    <select name="policy_type"><option value="free">Free</option><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select>
                </div>
                <div class="form-group"><label>Sharing</label><select name="sharing_type"><option value="">Any</option><option value="alone">Alone</option><option value="with_adult">With Adult</option></select></div>
                <div class="form-group"><label>Discount %</label><input type="number" name="discount_percentage" step="0.01"></div>
                <div class="form-group"><label>Discount Fixed</label><input type="number" name="discount_fixed" step="0.01"></div>
                <div class="form-group"><label>Room Type</label><select name="room_type_id"><option value="">All</option>@foreach($hotel->roomTypes as $rt)<option value="{{ $rt->id }}">{{ $rt->label ?? ucfirst(str_replace('_', ' ', $rt->type)) }}</option>@endforeach</select></div>
                <div class="form-group"><label>Meal Plan</label><select name="meal_plan_id"><option value="">All</option>@foreach($mealPlans as $mp)<option value="{{ $mp->id }}">{{ $mp->abbreviation ?? $mp->name }}</option>@endforeach</select></div>
                <div class="form-group">
                    <label>Season</label>
                    <select name="season_id">
                        <option value="">All</option>
                        @if($activeYear)
                            @foreach($activeYear->seasons as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group" style="flex:2;"><label>Notes</label><input type="text" name="notes"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($hotel->childPolicies->count())
            <table class="mini-table">
                <thead><tr><th>Age</th><th>Policy</th><th>Value</th><th>Share</th><th>%</th><th>Fixed</th><th>Room</th><th>Meal</th><th>Season</th><th>Notes</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($hotel->childPolicies as $cp)
                    <tr>
                        <td colspan="11">
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/child-policies/' . $cp->id) }}" class="inline-form" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <div class="form-group"><input type="number" name="min_age" min="0" max="17" value="{{ $cp->min_age }}" required></div>
                                <div class="form-group"><input type="number" name="max_age" min="0" max="17" value="{{ $cp->max_age }}" required></div>
                                <div class="form-group"><select name="policy_type" required><option value="free" {{ $cp->policy_type === 'free' ? 'selected' : '' }}>Free</option><option value="percentage" {{ $cp->policy_type === 'percentage' ? 'selected' : '' }}>Percentage</option><option value="fixed" {{ $cp->policy_type === 'fixed' ? 'selected' : '' }}>Fixed</option></select></div>
                                <div class="form-group"><input type="number" name="value" step="0.01" value="{{ $cp->value }}" required></div>
                                <div class="form-group"><select name="sharing_type"><option value="" {{ empty($cp->sharing_type) ? 'selected' : '' }}>Any</option><option value="alone" {{ $cp->sharing_type === 'alone' ? 'selected' : '' }}>Alone</option><option value="with_adult" {{ $cp->sharing_type === 'with_adult' ? 'selected' : '' }}>With Adult</option></select></div>
                                <div class="form-group"><input type="number" name="discount_percentage" step="0.01" value="{{ $cp->discount_percentage }}"></div>
                                <div class="form-group"><input type="number" name="discount_fixed" step="0.01" value="{{ $cp->discount_fixed }}"></div>
                                <div class="form-group"><select name="room_type_id"><option value="">All</option>@foreach($hotel->roomTypes as $rt)<option value="{{ $rt->id }}" {{ (int) $cp->room_type_id === (int) $rt->id ? 'selected' : '' }}>{{ $rt->label ?? ucfirst(str_replace('_', ' ', $rt->type)) }}</option>@endforeach</select></div>
                                <div class="form-group"><select name="meal_plan_id"><option value="">All</option>@foreach($mealPlans as $mp)<option value="{{ $mp->id }}" {{ (int) $cp->meal_plan_id === (int) $mp->id ? 'selected' : '' }}>{{ $mp->abbreviation ?? $mp->name }}</option>@endforeach</select></div>
                                <div class="form-group">
                                    <select name="season_id">
                                        <option value="">All</option>
                                        @if($activeYear)
                                            @foreach($activeYear->seasons as $s)
                                                <option value="{{ $s->id }}" {{ (int) $cp->season_id === (int) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group" style="flex:2;"><input type="text" name="notes" value="{{ $cp->notes }}"></div>
                                <button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
                            </form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/child-policies/' . $cp->id) }}" style="margin-top:6px;" onsubmit="return confirm('Delete child policy?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No child policies</p></div>
            @endif
        </div>

        {{-- Sub: Cancellation --}}
        <div data-subtab-content="sub-cancel" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/cancellation-policies') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Days Before</label><input type="number" name="days_before" min="0" required></div>
                <div class="form-group"><label>Penalty %</label><input type="number" name="penalty_percentage" step="0.01" required></div>
                <div class="form-group"><label>Description</label><input type="text" name="description"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($hotel->cancellationPolicies->count())
            <table class="mini-table">
                <thead><tr><th>Days Before</th><th>Penalty</th><th>Description</th><th></th></tr></thead>
                <tbody>
                    @foreach($hotel->cancellationPolicies as $cp)
                    <tr>
                        <td colspan="4">
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/cancellation-policies/' . $cp->id) }}" class="inline-form" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <div class="form-group"><input type="number" name="days_before" min="0" value="{{ $cp->days_before }}" required></div>
                                <div class="form-group"><input type="number" name="penalty_percentage" step="0.01" value="{{ $cp->penalty_percentage }}" required></div>
                                <div class="form-group" style="flex:2;"><input type="text" name="description" value="{{ $cp->description }}"></div>
                                <button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
                            </form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/cancellation-policies/' . $cp->id) }}" style="margin-top:6px;" onsubmit="return confirm('Delete cancellation policy?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No cancellation policies</p></div>
            @endif
        </div>

        {{-- Sub: Payment --}}
        <div data-subtab-content="sub-payment" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/payment-policies') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Title</label><input type="text" name="title" required placeholder="e.g. Deposit Terms"></div>
                <div class="form-group" style="flex:2;"><label>Content</label><input type="text" name="content" required placeholder="e.g. 30% deposit on confirmation, balance 30 days before arrival"></div>
                <div class="form-group"><label>Days Before</label><input type="number" name="days_before" min="0"></div>
                <div class="form-group"><label>Percentage</label><input type="number" name="percentage" step="0.01"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($hotel->paymentPolicies->count())
            <table class="mini-table">
                <thead><tr><th>Title</th><th>Content</th><th>Days</th><th>%</th><th></th></tr></thead>
                <tbody>
                    @foreach($hotel->paymentPolicies as $pp)
                    <tr>
                        <td colspan="5">
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/payment-policies/' . $pp->id) }}" class="inline-form" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <div class="form-group"><input type="text" name="title" value="{{ $pp->title }}" required></div>
                                <div class="form-group" style="flex:2;"><input type="text" name="content" value="{{ $pp->content }}" required></div>
                                <div class="form-group"><input type="number" name="days_before" min="0" value="{{ $pp->days_before }}"></div>
                                <div class="form-group"><input type="number" name="percentage" step="0.01" value="{{ $pp->percentage }}"></div>
                                <button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
                            </form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/payment-policies/' . $pp->id) }}" style="margin-top:6px;" onsubmit="return confirm('Delete payment policy?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No payment policies</p></div>
            @endif
        </div>

        {{-- Sub: Tour Leader Discounts --}}
        <div data-subtab-content="sub-tour-leader" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/tour-leader-discounts') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Min Pax</label><input type="number" name="min_pax" min="1" required></div>
                <div class="form-group"><label>Max Pax</label><input type="number" name="max_pax" min="1"></div>
                <div class="form-group"><label>Type</label><select name="discount_type" required><option value="free">Free</option><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></div>
                <div class="form-group"><label>Value</label><input type="number" name="value" step="0.01" required></div>
                <div class="form-group"><label>Discount %</label><input type="number" name="discount_percentage" step="0.01"></div>
                <div class="form-group" style="flex:2;"><label>Notes</label><input type="text" name="notes"></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Add</button>
            </form>
            @if($hotel->tourLeaderDiscounts->count())
            <table class="mini-table">
                <thead><tr><th>Pax</th><th>Type</th><th>Value</th><th>%</th><th>Notes</th><th></th></tr></thead>
                <tbody>
                    @foreach($hotel->tourLeaderDiscounts as $discount)
                    <tr>
                        <td colspan="6">
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/tour-leader-discounts/' . $discount->id) }}" class="inline-form" style="margin:0;">
                                @csrf
                                @method('PUT')
                                <div class="form-group"><input type="number" name="min_pax" min="1" value="{{ $discount->min_pax }}" required></div>
                                <div class="form-group"><input type="number" name="max_pax" min="1" value="{{ $discount->max_pax }}"></div>
                                <div class="form-group"><select name="discount_type" required><option value="free" {{ $discount->discount_type === 'free' ? 'selected' : '' }}>Free</option><option value="percentage" {{ $discount->discount_type === 'percentage' ? 'selected' : '' }}>Percentage</option><option value="fixed" {{ $discount->discount_type === 'fixed' ? 'selected' : '' }}>Fixed</option></select></div>
                                <div class="form-group"><input type="number" name="value" step="0.01" value="{{ $discount->value }}" required></div>
                                <div class="form-group"><input type="number" name="discount_percentage" step="0.01" value="{{ $discount->discount_percentage }}"></div>
                                <div class="form-group" style="flex:2;"><input type="text" name="notes" value="{{ $discount->notes }}"></div>
                                <button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Save</button>
                            </form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/tour-leader-discounts/' . $discount->id) }}" style="margin-top:6px;" onsubmit="return confirm('Delete tour leader rule?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No tour leader discounts</p></div>
            @endif
        </div>

        {{-- Sub: Backups --}}
        <div data-subtab-content="sub-backups" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/backup-rates') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Label</label><input type="text" name="label" placeholder="e.g. April contract pass"></div>
                <div class="form-group"><label>Rate Year</label><select name="rate_year_id"><option value="">Active Year</option>@foreach($hotel->rateYears as $ry)<option value="{{ $ry->id }}">{{ $ry->year }}</option>@endforeach</select></div>
                <button type="submit" class="btn btn-primary btn-sm" {{ !$canManageAccommodation ? 'disabled' : '' }}>+ Snapshot</button>
            </form>
            @if($hotel->backupRates->count())
            <table class="mini-table">
                <thead><tr><th>Version</th><th>Label</th><th>Date</th><th>Rate Year</th><th>Rows</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($hotel->backupRates->sortByDesc('id') as $backup)
                    <tr>
                        <td style="font-weight:700;">v{{ $backup->version_no ?? 'n/a' }}</td>
                        <td>{{ $backup->label }}</td>
                        <td>{{ $backup->snapshot_date?->format('Y-m-d') ?? $backup->created_at?->format('Y-m-d') }}</td>
                        <td>{{ $backup->sourceRateYear?->year ?? '—' }}</td>
                        <td>{{ count((array) data_get($backup->rate_data, 'rows', [])) }}</td>
                        <td>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/backup-rates/' . $backup->id . '/restore') }}" style="display:inline-block;">@csrf<button class="btn btn-success btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Restore</button></form>
                            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/backup-rates/' . $backup->id) }}" style="display:inline-block;" onsubmit="return confirm('Delete snapshot?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs" {{ !$canManageAccommodation ? 'disabled' : '' }}>Delete</button></form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No snapshots yet</p></div>
            @endif
        </div>
    </div>
</div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
initTabs('mainTabs');
initSubTabs('ratesSubTabs');
</script>
@endsection
