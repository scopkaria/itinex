@extends('layouts.app')
@section('title', ($provider->name ?? 'New') . ' — Transport — Itinex')
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
    .inline-form { display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; padding:16px; background:var(--bg-table-head); border-radius:var(--radius-sm); margin-bottom:16px; }
    .inline-form .form-group { margin-bottom:0; flex:1; min-width:120px; }
    .inline-form .form-group label { font-size:11px; font-weight:600; color:var(--text-secondary); margin-bottom:4px; display:block; }
    .inline-form .form-group input, .inline-form .form-group select { font-size:12px; padding:6px 10px; }
    .section-title { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border-light); }
    .scope-safari { background:var(--color-success-light); color:#166534; }
    .scope-transfer { background:var(--color-info-light); color:#1e40af; }
    .scope-both { background:var(--color-primary-light); color:#4338ca; }
    .doc-card { display:flex; align-items:center; gap:16px; padding:14px 16px; border:1px solid var(--border-color); border-radius:var(--radius-sm); margin-bottom:8px; }
    .doc-card .doc-icon { width:40px; height:40px; border-radius:var(--radius-sm); background:var(--color-primary-light); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .doc-card .doc-info { flex:1; }
    .doc-card .doc-info .doc-title { font-weight:600; font-size:13px; }
    .doc-card .doc-info .doc-meta { font-size:11px; color:var(--text-muted); }
</style>
@endsection
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'transport'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Transport Providers</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="detail-header">
                <div>
                    <a href="{{ url('/transport-providers') }}" class="back-link">&larr; Back to Transport Providers</a>
                    <h2>{{ $provider->name }}</h2>
                </div>
                @if($provider->is_active)<span class="badge badge-green">Active</span>@else <span class="badge badge-gray">Inactive</span>@endif
            </div>

            <div class="tab-container" id="mainTabs">
                <div class="tab-nav">
                    <button class="active" data-tab="tab-overview">Overview</button>
                    <button data-tab="tab-vtypes">Vehicle Types</button>
                    <button data-tab="tab-vehicles">Vehicles</button>
                    <button data-tab="tab-drivers">Drivers</button>
                    <button data-tab="tab-routes">Transfer Routes</button>
                    <button data-tab="tab-fuel">Fuel Costs</button>
                    <button data-tab="tab-pricing">Pricing</button>
                    <button data-tab="tab-docs">Documents</button>
                </div>
                <div class="tab-body">

{{-- ═══ Tab 1: Overview ═══ --}}
<div class="tab-content active" id="tab-overview">
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id) }}">
        @csrf @method('PUT')
        <div class="section-title">General Details</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Provider Name *</label><input type="text" name="name" value="{{ $provider->name }}" required></div>
            <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" value="{{ $provider->contact_person }}"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ $provider->phone }}"></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ $provider->email }}"></div>
        </div>
        <div class="form-group" style="margin-bottom:24px;"><label>Description</label><textarea name="description" rows="3">{{ $provider->description }}</textarea></div>
        <div class="section-title">VAT &amp; Markup</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group">
                <label>VAT Type</label>
                <select name="vat_type">
                    @foreach(['inclusive','exclusive','exempt'] as $v)<option value="{{ $v }}" {{ $provider->vat_type == $v ? 'selected' : '' }}>{{ ucfirst($v) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Markup %</label><input type="number" name="markup" step="0.01" value="{{ $provider->markup }}"></div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active">
                    <option value="1" {{ $provider->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$provider->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="sticky-save"><button type="submit" class="btn btn-primary">Save Overview</button></div>
    </form>
</div>

{{-- ═══ Tab 2: Vehicle Types ═══ --}}
<div class="tab-content" id="tab-vtypes">
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/vehicle-types') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. Land Cruiser"></div>
        <div class="form-group"><label>Capacity</label><input type="number" name="capacity" min="1" required></div>
        <div class="form-group"><label>Category</label>
            <select name="category"><option value="4x4">4x4</option><option value="minibus">Minibus</option><option value="coach">Coach</option><option value="sedan">Sedan</option></select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->vehicleTypes->count())
    <table class="mini-table">
        <thead><tr><th>Name</th><th>Capacity</th><th>Category</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->vehicleTypes as $vt)
            <tr>
                <td style="font-weight:600;">{{ $vt->name }}</td>
                <td>{{ $vt->capacity }}</td>
                <td><span class="badge badge-gray">{{ $vt->category ?? '—' }}</span></td>
                <td><form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/vehicle-types/' . $vt->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No vehicle types</p></div>
    @endif
</div>

{{-- ═══ Tab 3: Vehicles ═══ --}}
<div class="tab-content" id="tab-vehicles">
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/vehicles') }}">
        @csrf
        <div style="padding:16px;background:var(--bg-table-head);border-radius:var(--radius-sm);margin-bottom:16px;">
            <div class="info-grid-3" style="margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;"><label>Registration *</label><input type="text" name="registration_number" required></div>
                <div class="form-group" style="margin-bottom:0;"><label>Manufacturer</label><input type="text" name="manufacturer" placeholder="e.g. Toyota"></div>
                <div class="form-group" style="margin-bottom:0;"><label>Model</label><input type="text" name="model" placeholder="e.g. Land Cruiser"></div>
            </div>
            <div class="info-grid-3" style="margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;"><label>Type</label>
                    <select name="vehicle_type_id"><option value="">—</option>@foreach($provider->vehicleTypes as $vt)<option value="{{ $vt->id }}">{{ $vt->name }}</option>@endforeach</select>
                </div>
                <div class="form-group" style="margin-bottom:0;"><label>Branch</label><input type="text" name="branch" placeholder="e.g. Arusha"></div>
                <div class="form-group" style="margin-bottom:0;"><label>Driver</label>
                    <select name="driver_id"><option value="">—</option>@foreach($provider->drivers as $dr)<option value="{{ $dr->id }}">{{ $dr->name }}</option>@endforeach</select>
                </div>
            </div>
            <div class="info-grid-3" style="margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;"><label>Fuel Type</label>
                    <select name="fuel_type"><option value="diesel">Diesel</option><option value="petrol">Petrol</option><option value="electric">Electric</option><option value="hybrid">Hybrid</option></select>
                </div>
                <div class="form-group" style="margin-bottom:0;"><label>Engine Number</label><input type="text" name="engine_number"></div>
                <div class="form-group" style="margin-bottom:0;"><label>Chassis Number</label><input type="text" name="chassis_number"></div>
            </div>
            <div class="info-grid-3" style="margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;"><label>Seats</label><input type="number" name="seats" min="1" max="100"></div>
                <div class="form-group" style="margin-bottom:0;"><label>Scope</label>
                    <select name="scope"><option value="both">Both</option><option value="safari">Safari</option><option value="transfer">Transfer</option></select>
                </div>
                <div class="form-group" style="margin-bottom:0;"><label>Fuel Consumption (L/100km)</label><input type="number" name="fuel_consumption" step="0.01"></div>
            </div>
            <div class="info-grid-3" style="margin-bottom:0;">
                <div class="form-group" style="margin-bottom:0;"><label>Year</label><input type="number" name="year_of_manufacture" min="1990" max="2050"></div>
                <div class="form-group" style="margin-bottom:0;"><label>Color</label><input type="text" name="color"></div>
                <div class="form-group" style="margin-bottom:0;"><label>Status</label>
                    <select name="status"><option value="available">Available</option><option value="in_service">In Service</option><option value="maintenance">Maintenance</option></select>
                </div>
            </div>
            <div style="margin-top:12px;text-align:right;"><button type="submit" class="btn btn-primary btn-sm">+ Add Vehicle</button></div>
        </div>
    </form>
    @if($provider->vehicles->count())
    <table class="mini-table">
        <thead><tr><th>Registration</th><th>Manufacturer</th><th>Model</th><th>Type</th><th>Seats</th><th>Scope</th><th>Driver</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->vehicles as $v)
            <tr>
                <td style="font-weight:600;">{{ $v->registration_number ?? '—' }}</td>
                <td>{{ $v->manufacturer ?? ($v->make_model ?? '—') }}</td>
                <td>{{ $v->model ?? '—' }}</td>
                <td>{{ $v->vehicleType?->name ?? '—' }}</td>
                <td>{{ $v->seats ?? '—' }}</td>
                <td>@if($v->scope)<span class="badge scope-{{ $v->scope }}">{{ ucfirst($v->scope) }}</span>@else —@endif</td>
                <td>{{ $v->driver?->name ?? '—' }}</td>
                <td>
                    @if($v->status === 'available')<span class="badge badge-green">Available</span>
                    @elseif($v->status === 'in_service')<span class="badge badge-blue">In Service</span>
                    @else <span class="badge badge-amber">Maintenance</span>@endif
                </td>
                <td><form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/vehicles/' . $v->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No vehicles registered</p></div>
    @endif
</div>

{{-- ═══ Tab 4: Drivers ═══ --}}
<div class="tab-content" id="tab-drivers">
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/drivers') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
        <div class="form-group"><label>License #</label><input type="text" name="license_number"></div>
        <div class="form-group"><label>License Expiry</label><input type="date" name="license_expiry"></div>
        <div class="form-group"><label>Status</label>
            <select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->drivers->count())
    <table class="mini-table">
        <thead><tr><th>Name</th><th>Phone</th><th>License</th><th>Expiry</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->drivers as $dr)
            <tr>
                <td style="font-weight:600;">{{ $dr->name }}</td>
                <td>{{ $dr->phone ?? '—' }}</td>
                <td>{{ $dr->license_number ?? '—' }}</td>
                <td>{{ $dr->license_expiry ?? '—' }}</td>
                <td>@if($dr->status === 'active')<span class="badge badge-green">Active</span>@else <span class="badge badge-gray">Inactive</span>@endif</td>
                <td><form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/drivers/' . $dr->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No drivers</p></div>
    @endif
</div>

{{-- ═══ Tab 5: Transfer Routes ═══ --}}
<div class="tab-content" id="tab-routes">
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/transfer-routes') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>From</label><input type="text" name="origin" required placeholder="e.g. JRO Airport"></div>
        <div class="form-group"><label>To</label><input type="text" name="destination" required placeholder="e.g. Arusha Hotel"></div>
        <div class="form-group"><label>Distance (km)</label><input type="number" name="distance_km" step="0.1"></div>
        <div class="form-group"><label>Duration (min)</label><input type="number" name="duration_minutes" min="1"></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->transferRoutes->count())
    <table class="mini-table">
        <thead><tr><th>From → To</th><th>Distance</th><th>Duration</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->transferRoutes as $tr)
            <tr>
                <td style="font-weight:600;">{{ $tr->origin }} → {{ $tr->destination }}</td>
                <td>{{ $tr->distance_km ? number_format($tr->distance_km, 1) . ' km' : '—' }}</td>
                <td>{{ $tr->duration_minutes ? $tr->duration_minutes . ' min' : '—' }}</td>
                <td><form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/transfer-routes/' . $tr->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No transfer routes</p></div>
    @endif
</div>

{{-- ═══ Tab 6: Fuel Costs ═══ --}}
<div class="tab-content" id="tab-fuel">
    @php $cs = $provider->costSettings; @endphp
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/cost-settings') }}">
        @csrf @method('PUT')
        <div class="section-title">Fuel &amp; Operating Costs</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Fuel Cost per Litre ($)</label><input type="number" name="fuel_cost_per_litre" step="0.01" value="{{ $cs?->fuel_cost_per_litre ?? '' }}" required></div>
            <div class="form-group"><label>Driver Daily Rate ($)</label><input type="number" name="driver_daily_rate" step="0.01" value="{{ $cs?->driver_daily_rate ?? '' }}" required></div>
            <div class="form-group"><label>Insurance Daily ($)</label><input type="number" name="insurance_daily" step="0.01" value="{{ $cs?->insurance_daily ?? '' }}" required></div>
            <div class="form-group"><label>Maintenance Reserve ($)</label><input type="number" name="maintenance_reserve" step="0.01" value="{{ $cs?->maintenance_reserve ?? '' }}" required></div>
        </div>
        <div class="form-group" style="margin-bottom:24px;"><label>Notes</label><textarea name="notes" rows="3">{{ $cs?->notes ?? '' }}</textarea></div>
        <div class="sticky-save"><button type="submit" class="btn btn-primary">Save Fuel Costs</button></div>
    </form>
</div>

{{-- ═══ Tab 7: Pricing ═══ --}}
<div class="tab-content" id="tab-pricing">
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/rates') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Vehicle Type</label>
            <select name="vehicle_type_id" required><option value="">—</option>@foreach($provider->vehicleTypes as $vt)<option value="{{ $vt->id }}">{{ $vt->name }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>Route</label>
            <select name="transfer_route_id"><option value="">—</option>@foreach($provider->transferRoutes as $tr)<option value="{{ $tr->id }}">{{ $tr->origin }} → {{ $tr->destination }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>Type</label>
            <select name="rate_type"><option value="per_trip">Per Trip</option><option value="per_day">Per Day</option><option value="per_km">Per Km</option></select>
        </div>
        <div class="form-group"><label>Rate ($)</label><input type="number" name="rate" step="0.01" required></div>
        <div class="form-group"><label>Season</label><input type="text" name="season_name" placeholder="e.g. Peak"></div>
        <div class="form-group"><label>Valid From</label><input type="date" name="valid_from"></div>
        <div class="form-group"><label>Valid To</label><input type="date" name="valid_to"></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->rates->count())
    <table class="mini-table">
        <thead><tr><th>Vehicle</th><th>Route</th><th>Type</th><th>Rate</th><th>Season</th><th>Valid</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->rates as $rate)
            <tr>
                <td>{{ $rate->vehicleType?->name ?? '—' }}</td>
                <td>{{ $rate->transferRoute ? $rate->transferRoute->origin . ' → ' . $rate->transferRoute->destination : '—' }}</td>
                <td><span class="badge badge-gray">{{ str_replace('_', ' ', $rate->rate_type) }}</span></td>
                <td style="font-weight:600;">${{ number_format($rate->rate, 2) }}</td>
                <td>{{ $rate->season_name ?? '—' }}</td>
                <td>{{ $rate->valid_from ?? '' }} {{ $rate->valid_to ? '→ ' . $rate->valid_to : '' }}</td>
                <td><form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/rates/' . $rate->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No pricing configured</p></div>
    @endif
</div>

{{-- ═══ Tab 8: Documents ═══ --}}
<div class="tab-content" id="tab-docs">
    <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/documents') }}" enctype="multipart/form-data" style="padding:16px;background:var(--bg-table-head);border-radius:var(--radius-sm);margin-bottom:20px;">
        @csrf
        <div class="info-grid" style="margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;"><label>Title *</label><input type="text" name="title" required></div>
            <div class="form-group" style="margin-bottom:0;"><label>Type</label>
                <select name="type"><option value="general">General</option><option value="license">License</option><option value="insurance">Insurance</option><option value="permit">Permit</option><option value="registration">Registration</option></select>
            </div>
        </div>
        <div class="info-grid" style="margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;"><label>File *</label><input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"></div>
            <div class="form-group" style="margin-bottom:0;"><label>Expiry Date</label><input type="date" name="expiry_date"></div>
        </div>
        <div class="form-group" style="margin-bottom:8px;"><label>Notes</label><input type="text" name="notes"></div>
        <div style="text-align:right;"><button type="submit" class="btn btn-primary btn-sm">Upload Document</button></div>
    </form>

    @if($provider->documents->count())
        @foreach($provider->documents as $doc)
        <div class="doc-card">
            <div class="doc-icon">&#128196;</div>
            <div class="doc-info">
                <div class="doc-title">{{ $doc->title }}</div>
                <div class="doc-meta">
                    <span class="badge badge-gray">{{ ucfirst($doc->type) }}</span>
                    &middot; {{ $doc->file_name }}
                    @if($doc->expiry_date)
                        &middot; Expires: {{ $doc->expiry_date->format('M d, Y') }}
                        @if($doc->expiry_date->isPast()) <span style="color:var(--color-danger);font-weight:600;">EXPIRED</span>@endif
                    @endif
                </div>
            </div>
            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline btn-xs">View</a>
            <form method="POST" action="{{ url('/transport-providers/' . $provider->id . '/documents/' . $doc->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form>
        </div>
        @endforeach
    @else
    <div class="empty-state"><p>No documents uploaded</p></div>
    @endif
</div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>initTabs('mainTabs');</script>
@endsection
