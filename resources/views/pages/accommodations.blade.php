@extends('layouts.app')
@section('title', 'Accommodation — Itinex')
@section('styles')
<style>
    .cat-tag { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .cat-budget { background: var(--color-warning-light); color: #92400e; }
    .cat-midrange { background: var(--color-info-light); color: #1e40af; }
    .cat-luxury { background: #fce7f3; color: #9d174d; }
    .status-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 5px; }
    .status-dot.active { background: var(--color-success); }
    .status-dot.inactive { background: var(--text-muted); }
    .import-section { display:flex; align-items:center; gap:8px; padding:12px 16px; background:var(--bg-table-head); border-radius:var(--radius-sm); margin-bottom:16px; }
    .import-section label { font-size:12px; font-weight:600; color:var(--text-secondary); white-space:nowrap; }
    .pagination-wrap { display:flex; justify-content:center; margin-top:16px; }
    .pagination-wrap nav { display:flex; gap:4px; align-items:center; }
    .pagination-wrap nav a, .pagination-wrap nav span {
        display:inline-flex; align-items:center; justify-content:center;
        min-width:32px; height:32px; padding:0 8px;
        font-size:12px; border-radius:var(--radius-sm);
        border:1px solid var(--border-color); text-decoration:none; color:var(--text-secondary);
    }
    .pagination-wrap nav span[aria-current] { background:var(--color-primary); color:#fff; border-color:var(--color-primary); font-weight:600; }
    .pagination-wrap nav a:hover { background:var(--bg-hover); }
    .pagination-wrap nav span.disabled { opacity:.4; }
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
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="toast toast-error">{{ session('error') }}</div>@endif

            <div class="page-header">
                <h2>All Accommodation <span style="color:var(--text-muted);font-weight:400;font-size:14px;">({{ $accommodations->total() }})</span></h2>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-outline" onclick="openDrawer('importDrawer')">&#128228; CSV Import</button>
                    <button class="btn btn-primary" onclick="openDrawer('addDrawer')">+ Add Accommodation</button>
                </div>
            </div>

            {{-- Server-side filter bar --}}
            <form method="GET" action="{{ url('/accommodations') }}" id="filterForm">
                <div class="filter-bar">
                    <input type="text" name="search" class="search-input" placeholder="Search by name…" value="{{ request('search') }}">
                    <select name="location" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Locations</option>
                        @foreach($destinations as $d)<option value="{{ $d->id }}" {{ request('location') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                    </select>
                    <select name="chain" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Chains</option>
                        @foreach($chainsQuery as $ch)<option value="{{ $ch }}" {{ request('chain') == $ch ? 'selected' : '' }}>{{ $ch }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    @if(request()->hasAny(['search','location','chain','letter']))
                        <a href="{{ url('/accommodations') }}" class="btn btn-outline btn-sm">Clear</a>
                    @endif
                </div>

                {{-- Alphabet bar --}}
                <div class="alpha-bar" style="margin-bottom:16px;">
                    <a href="{{ url('/accommodations') . '?' . http_build_query(request()->except('letter','page')) }}"
                       class="{{ !request('letter') ? 'active' : '' }}" style="text-decoration:none;">All</a>
                    @foreach(range('A','Z') as $letter)
                        <a href="{{ url('/accommodations') . '?' . http_build_query(array_merge(request()->except('page'), ['letter' => $letter])) }}"
                           class="{{ request('letter') === $letter ? 'active' : '' }}" style="text-decoration:none;">{{ $letter }}</a>
                    @endforeach
                </div>
            </form>

            @if($accommodations->count())
            <div class="card" style="padding:0;overflow:hidden;">
                <div class="table-wrap">
                    <table id="accTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Chain</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th style="width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accommodations as $idx => $a)
                            <tr>
                                <td style="color:var(--text-muted);font-size:12px;">{{ $accommodations->firstItem() + $idx }}</td>
                                <td style="font-weight:600;color:var(--text-primary);">{{ $a->name }}</td>
                                <td>{{ $a->location?->name ?? '—' }}</td>
                                <td>{{ $a->chain ?? '—' }}</td>
                                <td><span class="cat-tag cat-{{ $a->category }}">{{ $a->category }}</span></td>
                                <td><span class="status-dot {{ $a->is_active ? 'active' : 'inactive' }}"></span>{{ $a->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <div class="action-cell">
                                        <a href="{{ url('/accommodations/' . $a->id . '/edit') }}" class="act-open">Open</a>
                                        <form method="POST" action="{{ url('/accommodations/' . $a->id) }}" onsubmit="return confirm('Delete {{ addslashes($a->name) }}?')" class="delete-form">
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
            <div class="pagination-wrap">{{ $accommodations->links() }}</div>
            @else
            <div class="card">
                <div class="empty-state">
                    <div class="empty-icon">&#127976;</div>
                    <p>No accommodation found</p>
                    <button class="btn btn-primary" style="margin-top:16px;" onclick="openDrawer('addDrawer')">+ Add Your First Accommodation</button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Add Drawer --}}
<div class="drawer-overlay" id="addDrawer-overlay" onclick="closeDrawer('addDrawer')"></div>
<div class="side-drawer" id="addDrawer">
    <div class="drawer-header">
        <h3>Add Accommodation</h3>
        <button class="drawer-close" onclick="closeDrawer('addDrawer')">&times;</button>
    </div>
    <form method="POST" action="{{ url('/accommodations') }}">
        @csrf
        <div class="drawer-body">
            @include('partials.company-selector')
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group">
                <label>Location *</label>
                <select name="location_id" required>
                    <option value="">Select location</option>
                    @foreach($destinations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="budget">Budget</option>
                        <option value="midrange" selected>Midrange</option>
                        <option value="luxury">Luxury</option>
                    </select>
                </div>
                <div class="form-group"><label>Chain</label><input type="text" name="chain" placeholder="e.g. Serena Hotels"></div>
            </div>
            <div class="form-row cols-2">
                <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
            </div>
            <div class="form-row cols-2">
                <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                <div class="form-group"><label>Website</label><input type="url" name="website" placeholder="https://"></div>
            </div>
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

{{-- CSV Import Drawer --}}
<div class="drawer-overlay" id="importDrawer-overlay" onclick="closeDrawer('importDrawer')"></div>
<div class="side-drawer" id="importDrawer">
    <div class="drawer-header">
        <h3>Bulk CSV Import</h3>
        <button class="drawer-close" onclick="closeDrawer('importDrawer')">&times;</button>
    </div>
    <form method="POST" action="{{ url('/accommodations/import-csv') }}" enctype="multipart/form-data">
        @csrf
        <div class="drawer-body">
            @include('partials.company-selector')
            <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;">
                Upload a CSV file with columns: <strong>name, chain, location</strong><br>
                Use <code>NA</code> or leave blank for no chain. Locations will be matched or created automatically.
            </p>
            <div class="form-group"><label>CSV File *</label><input type="file" name="csv_file" accept=".csv,.txt" required></div>
            <div style="background:var(--bg-table-head);border-radius:var(--radius-sm);padding:12px 16px;font-size:12px;margin-top:12px;">
                <strong>Example CSV format:</strong>
                <pre style="margin:8px 0 0;font-size:11px;color:var(--text-muted);">name,chain,location
SERENA LODGE,SERENA HOTELS,SERENGETI NATIONAL PARK
BUSH CAMP,NA,TARANGIRE NATIONAL PARK</pre>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" onclick="closeDrawer('importDrawer')">Cancel</button>
            <button type="submit" class="btn btn-primary">Import</button>
        </div>
    </form>
</div>
@endsection
