@extends('layouts.app')
@section('title', ($provider->name ?? 'New') . ' — Flights — Itinex')
@section('styles')
<style>
    .detail-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
    .detail-header h2 { font-size:22px; font-weight:700; }
    .detail-header .back-link { font-size:13px; color:var(--text-secondary); text-decoration:none; }
    .detail-header .back-link:hover { color:var(--color-primary); }
    .tab-container { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden; }
    .tab-body { padding:24px; }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    @media(max-width:768px) { .info-grid { grid-template-columns:1fr; } }
    .mini-table { width:100%; border-collapse:collapse; font-size:13px; }
    .mini-table th { text-align:left; padding:8px 12px; background:var(--bg-table-head); border-bottom:1px solid var(--border-color); font-weight:600; color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
    .mini-table td { padding:8px 12px; border-bottom:1px solid var(--border-light); }
    .inline-form { display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; padding:16px; background:var(--bg-table-head); border-radius:var(--radius-sm); margin-bottom:16px; }
    .inline-form .form-group { margin-bottom:0; flex:1; min-width:120px; }
    .inline-form .form-group label { font-size:11px; font-weight:600; color:var(--text-secondary); margin-bottom:4px; display:block; }
    .inline-form .form-group input, .inline-form .form-group select { font-size:12px; padding:6px 10px; }
    .section-title { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border-light); }
</style>
@endsection
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'flights'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Flight Providers</h2>
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
                    <a href="{{ url('/flight-providers') }}" class="back-link">&larr; Back to Flight Providers</a>
                    <h2>{{ $provider->name }}</h2>
                </div>
                @if($provider->is_active)<span class="badge badge-green">Active</span>@else <span class="badge badge-gray">Inactive</span>@endif
            </div>

            <div class="tab-container" id="mainTabs">
                <div class="tab-nav">
                    <button class="active" data-tab="tab-overview">Overview</button>
                    <button data-tab="tab-aircraft">Aircraft Types</button>
                    <button data-tab="tab-routes">Routes</button>
                    <button data-tab="tab-seasonal">Seasonal Rates</button>
                    <button data-tab="tab-charter">Charter Rates</button>
                    <button data-tab="tab-child">Child Pricing</button>
                    <button data-tab="tab-policies">Policies</button>
                </div>
                <div class="tab-body">

{{-- ═══ Tab 1: Overview ═══ --}}
<div class="tab-content active" id="tab-overview">
    <form method="POST" action="{{ url('/flight-providers/' . $provider->id) }}">
        @csrf @method('PUT')
        <div class="section-title">General Details</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Provider Name *</label><input type="text" name="name" value="{{ $provider->name }}" required></div>
            <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" value="{{ $provider->contact_person }}"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ $provider->phone }}"></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ $provider->email }}"></div>
            <div class="form-group"><label>Website</label><input type="url" name="website" value="{{ $provider->website }}"></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3">{{ $provider->description }}</textarea></div>
        </div>
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

{{-- ═══ Tab 2: Aircraft Types ═══ --}}
<div class="tab-content" id="tab-aircraft">
    <form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/aircraft-types') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. Cessna 208"></div>
        <div class="form-group"><label>Code</label><input type="text" name="code" placeholder="C208"></div>
        <div class="form-group"><label>Capacity</label><input type="number" name="capacity" min="1"></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->aircraftTypes->count())
    <table class="mini-table">
        <thead><tr><th>Name</th><th>Code</th><th>Capacity</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->aircraftTypes as $at)
            <tr>
                <td style="font-weight:600;">{{ $at->name }}</td>
                <td>{{ $at->code ?? '—' }}</td>
                <td>{{ $at->capacity ?? '—' }}</td>
                <td><form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/aircraft-types/' . $at->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No aircraft types</p></div>
    @endif
</div>

{{-- ═══ Tab 3: Routes ═══ --}}
<div class="tab-content" id="tab-routes">
    <form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/routes') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>From</label>
            <select name="origin_id" required><option value="">Select</option>@foreach($destinations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>To</label>
            <select name="arrival_id" required><option value="">Select</option>@foreach($destinations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>Duration (min)</label><input type="number" name="flight_duration_minutes" min="1"></div>
        <div class="form-group"><label>Distance (km)</label><input type="number" name="distance_km" step="0.1"></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->routes->count())
    <table class="mini-table">
        <thead><tr><th>From → To</th><th>Duration</th><th>Distance</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->routes as $r)
            <tr>
                <td style="font-weight:600;">{{ $r->originDestination?->name ?? '?' }} → {{ $r->arrivalDestination?->name ?? '?' }}</td>
                <td>{{ $r->flight_duration_minutes ? $r->flight_duration_minutes . ' min' : '—' }}</td>
                <td>{{ $r->distance_km ? number_format($r->distance_km, 1) . ' km' : '—' }}</td>
                <td><form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/routes/' . $r->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No routes</p></div>
    @endif
</div>

{{-- ═══ Tab 4: Seasonal Rates ═══ --}}
<div class="tab-content" id="tab-seasonal">
    <form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/seasonal-rates') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Route</label>
            <select name="route_id" required><option value="">Select</option>@foreach($provider->routes as $r)<option value="{{ $r->id }}">{{ $r->originDestination?->name ?? '?' }} → {{ $r->arrivalDestination?->name ?? '?' }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>Aircraft</label>
            <select name="aircraft_type_id"><option value="">—</option>@foreach($provider->aircraftTypes as $at)<option value="{{ $at->id }}">{{ $at->name }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>Season</label><input type="text" name="season_name" placeholder="e.g. Peak"></div>
        <div class="form-group"><label>Rate</label><input type="number" name="rate" step="0.01" required></div>
        <div class="form-group"><label>Valid From</label><input type="date" name="valid_from"></div>
        <div class="form-group"><label>Valid To</label><input type="date" name="valid_to"></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->seasonalRates->count())
    <table class="mini-table">
        <thead><tr><th>Route</th><th>Aircraft</th><th>Season</th><th>Rate</th><th>Period</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->seasonalRates as $sr)
            <tr>
                <td>{{ $sr->route?->originDestination?->name ?? '?' }} → {{ $sr->route?->arrivalDestination?->name ?? '?' }}</td>
                <td>{{ $sr->aircraftType?->name ?? '—' }}</td>
                <td>{{ $sr->season_name ?? '—' }}</td>
                <td style="font-weight:600;">${{ number_format($sr->rate, 2) }}</td>
                <td>{{ $sr->valid_from ?? '' }} {{ $sr->valid_to ? '→ ' . $sr->valid_to : '' }}</td>
                <td><form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/seasonal-rates/' . $sr->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No seasonal rates</p></div>
    @endif
</div>

{{-- ═══ Tab 5: Charter Rates ═══ --}}
<div class="tab-content" id="tab-charter">
    <form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/charter-flights') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Route</label>
            <select name="route_id" required><option value="">Select</option>@foreach($provider->routes as $r)<option value="{{ $r->id }}">{{ $r->originDestination?->name ?? '?' }} → {{ $r->arrivalDestination?->name ?? '?' }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>Aircraft</label>
            <select name="aircraft_type_id"><option value="">—</option>@foreach($provider->aircraftTypes as $at)<option value="{{ $at->id }}">{{ $at->name }}</option>@endforeach</select>
        </div>
        <div class="form-group"><label>Charter Rate</label><input type="number" name="charter_rate" step="0.01" required></div>
        <div class="form-group"><label>Min Passengers</label><input type="number" name="min_passengers" min="1"></div>
        <div class="form-group"><label>Max Passengers</label><input type="number" name="max_passengers" min="1"></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->charterFlights->count())
    <table class="mini-table">
        <thead><tr><th>Route</th><th>Aircraft</th><th>Charter Rate</th><th>Passengers</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->charterFlights as $cf)
            <tr>
                <td>{{ $cf->route?->originDestination?->name ?? '?' }} → {{ $cf->route?->arrivalDestination?->name ?? '?' }}</td>
                <td>{{ $cf->aircraftType?->name ?? '—' }}</td>
                <td style="font-weight:600;">${{ number_format($cf->charter_rate, 2) }}</td>
                <td>{{ $cf->min_passengers ?? '—' }} – {{ $cf->max_passengers ?? '—' }}</td>
                <td><form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/charter-flights/' . $cf->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No charter rates</p></div>
    @endif
</div>

{{-- ═══ Tab 6: Child Pricing ═══ --}}
<div class="tab-content" id="tab-child">
    <form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/child-pricing') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Age From</label><input type="number" name="age_from" min="0" max="17" required></div>
        <div class="form-group"><label>Age To</label><input type="number" name="age_to" min="0" max="17" required></div>
        <div class="form-group"><label>Discount %</label><input type="number" name="discount_percentage" step="0.01" required></div>
        <div class="form-group"><label>Policy</label>
            <select name="pricing_type"><option value="free">Free</option><option value="discounted">Discounted</option><option value="full_rate">Full Rate</option></select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->childPricing->count())
    <table class="mini-table">
        <thead><tr><th>Age Range</th><th>Policy</th><th>Discount</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->childPricing as $cp)
            <tr>
                <td style="font-weight:600;">{{ $cp->age_from }} – {{ $cp->age_to }} yrs</td>
                <td><span class="badge badge-blue">{{ ucfirst(str_replace('_', ' ', $cp->pricing_type)) }}</span></td>
                <td>{{ $cp->discount_percentage }}%</td>
                <td><form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/child-pricing/' . $cp->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No child pricing rules</p></div>
    @endif
</div>

{{-- ═══ Tab 7: Policies ═══ --}}
<div class="tab-content" id="tab-policies">
    <form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/policies') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Type</label>
            <select name="policy_type"><option value="cancellation">Cancellation</option><option value="baggage">Baggage</option><option value="change">Change</option><option value="general">General</option></select>
        </div>
        <div class="form-group" style="flex:2;"><label>Description</label><input type="text" name="description" required></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($provider->policies->count())
    <table class="mini-table">
        <thead><tr><th>Type</th><th>Description</th><th></th></tr></thead>
        <tbody>
            @foreach($provider->policies as $pol)
            <tr>
                <td><span class="badge badge-purple">{{ ucfirst($pol->policy_type) }}</span></td>
                <td>{{ $pol->description }}</td>
                <td><form method="POST" action="{{ url('/flight-providers/' . $provider->id . '/policies/' . $pol->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No policies</p></div>
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
