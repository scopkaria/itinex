@extends('layouts.app')
@section('title', 'Transport — Itinex')
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

            <div class="page-header">
                <h2>Providers <span style="color:var(--text-muted);font-weight:400;font-size:14px;">({{ $providers->count() }})</span></h2>
                <button class="btn btn-primary" onclick="openDrawer('addDrawer')">+ Add Provider</button>
            </div>

            <div class="filter-bar">
                <input type="text" id="tSearch" class="search-input" placeholder="Search providers…" oninput="filterTransport()">
            </div>

            @if($providers->count())
            <div class="card" style="padding:0;overflow:hidden;">
                <div class="table-wrap">
                    <table id="tTable">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Contact</th>
                                <th>Vehicles</th>
                                <th>Drivers</th>
                                <th>Status</th>
                                <th style="width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providers as $p)
                            <tr data-search="{{ strtolower($p->name . ' ' . ($p->contact_person ?? '')) }}">
                                <td style="font-weight:600;color:var(--text-primary);">{{ $p->name }}</td>
                                <td>{{ $p->contact_person ?? '—' }}</td>
                                <td><span class="badge badge-blue">{{ $p->vehicles_count }}</span></td>
                                <td><span class="badge badge-purple">{{ $p->drivers_count }}</span></td>
                                <td>
                                    @if($p->is_active)<span class="badge badge-green">Active</span>
                                    @else <span class="badge badge-gray">Inactive</span>@endif
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <a href="{{ url('/transport-providers/' . $p->id . '/edit') }}" class="act-open">Open</a>
                                        <a href="{{ url('/transport-providers/' . $p->id . '/edit') }}" class="act-edit">Edit</a>
                                        <form method="POST" action="{{ url('/transport-providers/' . $p->id) }}" onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')" class="delete-form">
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
                    <div class="empty-icon">&#128663;</div>
                    <p>No transport providers yet</p>
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
        <h3>Add Transport Provider</h3>
        <button class="drawer-close" onclick="closeDrawer('addDrawer')">&times;</button>
    </div>
    <form method="POST" action="{{ url('/transport-providers') }}">
        @csrf
        <div class="drawer-body">
            @include('partials.company-selector')
            <div class="form-group"><label>Provider Name *</label><input type="text" name="name" required></div>
            <div class="form-row cols-2">
                <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
            </div>
            <div class="form-group"><label>Email</label><input type="email" name="email"></div>
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
function filterTransport() {
    const q = document.getElementById('tSearch').value.toLowerCase();
    document.querySelectorAll('#tTable tbody tr').forEach(r => {
        r.style.display = r.dataset.search.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
