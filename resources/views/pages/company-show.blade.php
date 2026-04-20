@extends('layouts.app')
@section('title', $company->name . ' — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'companies'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">{{ $company->name }}</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            <div style="display:flex;gap:16px;align-items:center;margin-bottom:24px;">
                <a href="{{ url('/companies') }}" style="color:#4f46e5;font-size:14px;">&larr; Back to Companies</a>
                <h2 style="font-size:18px;font-weight:700;flex:1;">Company Overview</h2>
                <span class="badge {{ $company->is_active ? 'badge-green' : 'badge-red' }}" style="font-size:13px;padding:4px 14px;">{{ $company->is_active ? 'Active' : 'Inactive' }}</span>
            </div>

            {{-- Company info --}}
            <div class="card" style="margin-bottom:20px;display:flex;gap:32px;flex-wrap:wrap;align-items:center;">
                <div>
                    <div style="font-size:12px;color:#6b7280;">Email</div>
                    <div style="font-weight:600;">{{ $company->email ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:#6b7280;">Phone</div>
                    <div style="font-weight:600;">{{ $company->phone ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:#6b7280;">Created</div>
                    <div style="font-weight:600;">{{ $company->created_at->format('M d, Y') }}</div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="stats-grid" style="margin-bottom:28px;">
                <div class="stat-card">
                    <div class="stat-label">Users</div>
                    <div class="stat-value">{{ $company->users_count }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Destinations</div>
                    <div class="stat-value">{{ $company->destinations_count }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Fee Rates</div>
                    <div class="stat-value">{{ $company->destination_fees_count }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Hotels</div>
                    <div class="stat-value">{{ $company->hotels_count }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Vehicles</div>
                    <div class="stat-value">{{ $company->vehicles_count }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Itineraries</div>
                    <div class="stat-value">{{ $company->itineraries_count }}</div>
                </div>
            </div>

            {{-- Tabbed sections --}}
            <div style="display:flex;gap:8px;margin-bottom:20px;border-bottom:2px solid #e5e7eb;padding-bottom:0;">
                <button class="tab-btn active" onclick="showTab('users')" id="tab-users" style="padding:10px 20px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid #4f46e5;margin-bottom:-2px;color:#4f46e5;">Users ({{ $company->users_count }})</button>
                <button class="tab-btn" onclick="showTab('destinations')" id="tab-destinations" style="padding:10px 20px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:#6b7280;">Destinations ({{ $company->destinations_count }})</button>
                <button class="tab-btn" onclick="showTab('hotels')" id="tab-hotels" style="padding:10px 20px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:#6b7280;">Hotels ({{ $company->hotels_count }})</button>
                <button class="tab-btn" onclick="showTab('vehicles')" id="tab-vehicles" style="padding:10px 20px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:#6b7280;">Vehicles ({{ $company->vehicles_count }})</button>
                <button class="tab-btn" onclick="showTab('itineraries')" id="tab-itineraries" style="padding:10px 20px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:#6b7280;">Itineraries ({{ $company->itineraries_count }})</button>
            </div>

            {{-- USERS TAB --}}
            <div class="tab-panel" id="panel-users">
                <div class="card">
                    @if($users->count())
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
                            <tbody>
                                @foreach($users as $u)
                                <tr>
                                    <td style="font-weight:600;">{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td><span class="badge {{ $u->role === 'admin' ? 'badge-purple' : 'badge-blue' }}">{{ strtoupper($u->role) }}</span></td>
                                    <td>{{ $u->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state"><p>No users</p></div>
                    @endif
                </div>
            </div>

            {{-- DESTINATIONS TAB --}}
            <div class="tab-panel" id="panel-destinations" style="display:none;">
                @forelse($destinations as $d)
                <div class="card" style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <span style="font-weight:700;">{{ $d->name }}</span>
                            <span style="color:#6b7280;font-size:13px;margin-left:8px;">{{ $d->countryRef?->name ?? '' }}{{ $d->regionRef ? ' · ' . $d->regionRef->name : '' }}</span>
                            <span class="badge badge-amber" style="font-size:10px;margin-left:6px;">{{ strtoupper(str_replace('_', ' ', $d->category)) }}</span>
                        </div>
                        <span class="badge badge-blue">{{ $d->fees->count() }} {{ Str::plural('rate', $d->fees->count()) }}</span>
                    </div>
                    @if($d->fees->count())
                    <div class="table-wrap" style="margin-top:10px;">
                        <table>
                            <thead><tr><th>Fee Type</th><th>Season</th><th>NR Adult</th><th>NR Child</th><th>Res Adult</th><th>Res Child</th><th>Vehicle</th><th>Guide</th><th>VAT</th></tr></thead>
                            <tbody>
                                @foreach($d->fees as $fee)
                                <tr>
                                    <td><span class="badge badge-blue">{{ $fee->fee_type }}</span></td>
                                    <td><span class="badge badge-green">{{ $fee->season_name }}</span></td>
                                    <td style="font-weight:600;">${{ number_format($fee->nr_adult, 2) }}</td>
                                    <td>${{ number_format($fee->nr_child, 2) }}</td>
                                    <td>${{ number_format($fee->resident_adult, 2) }}</td>
                                    <td>${{ number_format($fee->resident_child, 2) }}</td>
                                    <td>${{ number_format($fee->vehicle_rate, 2) }}</td>
                                    <td>${{ number_format($fee->guide_rate, 2) }}</td>
                                    <td>{{ ucfirst($fee->vat_type) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                @empty
                <div class="card"><div class="empty-state"><p>No destinations</p></div></div>
                @endforelse
            </div>

            {{-- HOTELS TAB --}}
            <div class="tab-panel" id="panel-hotels" style="display:none;">
                <div class="card">
                    @if($hotels->count())
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Name</th><th>Location</th><th>Category</th><th>Created</th></tr></thead>
                            <tbody>
                                @foreach($hotels as $h)
                                <tr>
                                    <td style="font-weight:600;">{{ $h->name }}</td>
                                    <td>{{ $h->location?->name ?? '—' }}</td>
                                    <td><span class="badge badge-amber">{{ strtoupper($h->category) }}</span></td>
                                    <td>{{ $h->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state"><p>No hotels</p></div>
                    @endif
                </div>
            </div>

            {{-- VEHICLES TAB --}}
            <div class="tab-panel" id="panel-vehicles" style="display:none;">
                <div class="card">
                    @if($vehicles->count())
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Name</th><th>Capacity</th><th>$/Day</th></tr></thead>
                            <tbody>
                                @foreach($vehicles as $v)
                                <tr>
                                    <td style="font-weight:600;">{{ $v->name }}</td>
                                    <td>{{ $v->capacity }} pax</td>
                                    <td>${{ number_format($v->price_per_day, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state"><p>No vehicles</p></div>
                    @endif
                </div>
            </div>

            {{-- ITINERARIES TAB --}}
            <div class="tab-panel" id="panel-itineraries" style="display:none;">
                <div class="card">
                    @if($itineraries->count())
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Client</th><th>Title</th><th>People</th><th>Days</th><th>Cost</th><th>Price</th><th>Created</th></tr></thead>
                            <tbody>
                                @foreach($itineraries as $it)
                                <tr>
                                    <td style="font-weight:600;">{{ $it->client_name }}</td>
                                    <td>{{ $it->title ?? '—' }}</td>
                                    <td>{{ $it->number_of_people }}</td>
                                    <td>{{ $it->days_count }}</td>
                                    <td>${{ number_format($it->total_cost, 2) }}</td>
                                    <td style="color:#2563eb;">${{ number_format($it->total_price, 2) }}</td>
                                    <td>{{ $it->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state"><p>No itineraries</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(name) {
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.style.display = 'none'; });
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.style.borderBottomColor = 'transparent'; b.style.color = '#6b7280'; });
    document.getElementById('panel-' + name).style.display = 'block';
    var btn = document.getElementById('tab-' + name);
    btn.style.borderBottomColor = '#4f46e5';
    btn.style.color = '#4f46e5';
}
</script>
@endsection
