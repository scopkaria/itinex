@extends('layouts.app')
@section('title', 'Flights — Itinex')
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

            <div class="page-header">
                <h2>Airline Providers <span style="color:var(--text-muted);font-weight:400;font-size:14px;">({{ $providers->count() }})</span></h2>
                <button class="btn btn-primary" onclick="openDrawer('addDrawer')">+ Add Provider</button>
            </div>

            <div class="filter-bar">
                <input type="text" id="flightSearch" class="search-input" placeholder="Search providers…" oninput="filterFlights()">
            </div>

            @if($providers->count())
            <div class="card" style="padding:0;overflow:hidden;">
                <div class="table-wrap">
                    <table id="flightTable">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Contact</th>
                                <th>Routes</th>
                                <th>Aircraft</th>
                                <th>Status</th>
                                <th style="width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providers as $p)
                            <tr data-search="{{ strtolower($p->name . ' ' . ($p->contact_person ?? '')) }}">
                                <td style="font-weight:600;color:var(--text-primary);">{{ $p->name }}</td>
                                <td>{{ $p->contact_person ?? '—' }}</td>
                                <td><span class="badge badge-blue">{{ $p->routes_count }}</span></td>
                                <td><span class="badge badge-purple">{{ $p->aircraft_types_count }}</span></td>
                                <td>
                                    @if($p->is_active)<span class="badge badge-green">Active</span>
                                    @else <span class="badge badge-gray">Inactive</span>@endif
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <a href="{{ url('/flight-providers/' . $p->id . '/edit') }}" class="act-open">Open</a>
                                        <a href="{{ url('/flight-providers/' . $p->id . '/edit') }}" class="act-edit">Edit</a>
                                        <form method="POST" action="{{ url('/flight-providers/' . $p->id) }}" onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')" class="delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="act-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card">
                <div class="empty-state">
                    <div class="empty-icon">&#9992;</div>
                    <p>No flight providers yet</p>
                    <button class="btn btn-primary" style="margin-top:16px;" onclick="openDrawer('addDrawer')">+ Add Your First Provider</button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Add Drawer --}}
<div class="drawer-overlay" id="addDrawerOverlay" onclick="closeDrawer('addDrawer')"></div>
<div class="side-drawer" id="addDrawer">
    <div class="drawer-header">
        <h3>Add Flight Provider</h3>
        <button class="drawer-close" onclick="closeDrawer('addDrawer')">&times;</button>
    </div>
    <form method="POST" action="{{ url('/flight-providers') }}">
        @csrf
        <div class="drawer-body">
            @include('partials.company-selector')
            <div class="form-group"><label>Provider Name *</label><input type="text" name="name" required></div>
            <div class="form-row cols-2">
                <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
            </div>
            <div class="form-row cols-2">
                <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                <div class="form-group"><label>Website</label><input type="url" name="website" placeholder="https://"></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label>VAT Type</label>
                    <select name="vat_type"><option value="inclusive">Inclusive</option><option value="exclusive">Exclusive</option><option value="exempt">Exempt</option></select>
                </div>
                <div class="form-group"><label>Markup %</label><input type="number" name="markup" step="0.01" value="0"></div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" onclick="closeDrawer('addDrawer')">Cancel</button>
            <button type="submit" class="btn btn-primary">Create &amp; Open</button>
        </div>
    </form>
</div>
@endsection
@section('scripts')
<script>
function filterFlights() {
    const q = document.getElementById('flightSearch').value.toLowerCase();
    document.querySelectorAll('#flightTable tbody tr').forEach(r => {
        r.style.display = r.dataset.search.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
