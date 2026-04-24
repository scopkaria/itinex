@extends('layouts.app')
@section('title', 'Accommodation — Itinex')
@section('styles')
<style>
    .import-section { display:flex; align-items:center; gap:8px; padding:12px 16px; background:var(--bg-muted); border-radius:var(--radius-sm); margin-bottom:16px; }
    .import-section label { font-size:12px; font-weight:600; color:var(--text-secondary); white-space:nowrap; }
    .acc-row { display: flex; align-items: center; gap: 16px; padding: 14px 20px; border-bottom: 1px solid var(--border-light); transition: background var(--duration-fast); }
    .acc-row:hover { background: var(--bg-table-hover); }
    .acc-row:last-child { border-bottom: none; }
    .acc-num { width: 36px; font-size: 12px; color: var(--text-muted); font-weight: 600; text-align: center; flex-shrink: 0; }
    .acc-thumb { width: 48px; height: 48px; border-radius: var(--radius-md); background: var(--bg-muted); flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; }
    .acc-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .acc-thumb .placeholder-icon { color: var(--text-muted); font-size: 20px; opacity: 0.5; }
    .acc-info { flex: 1; min-width: 0; }
    .acc-name { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .acc-meta { font-size: 12px; color: var(--text-muted); display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .acc-meta svg { width: 12px; height: 12px; opacity: 0.6; }
    .acc-col { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
</style>
@endsection
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'accommodations'])
    <div class="main-content">
        <header class="topbar">
            <h2>Accommodation</h2>
            <div class="topbar-user">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="toast toast-error">{{ session('error') }}</div>@endif

            <div class="page-header">
                <h2>All Accommodation <span class="page-title-count">({{ $accommodations->total() }})</span></h2>
                <div class="header-actions">
                    <a class="btn btn-outline btn-sm" href="{{ url('/accommodations') }}?bulk_clone=1">
                        Clone Rates (Bulk)
                    </a>
                    <a class="btn btn-outline btn-sm" href="#csv-import-panel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        CSV Import
                    </a>
                    <a class="btn btn-primary" href="#add-accommodation-panel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Accommodation
                    </a>
                </div>
            </div>

            <div class="card" id="add-accommodation-panel" style="margin-bottom:16px;">
                <h3 style="margin-bottom:12px;">Add Accommodation</h3>
                <form method="POST" action="{{ url('/accommodations') }}">
                    @csrf
                    @include('partials.company-selector')
                    <div class="form-row cols-2">
                        <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                        <div class="form-group"><label>Chain</label><input type="text" name="chain" placeholder="e.g. Serena Hotels"></div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label>Location *</label>
                            <select name="location_id" required>
                                <option value="">Select location</option>
                                @foreach($destinations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" required>
                                <option value="budget">Budget</option>
                                <option value="midrange" selected>Midrange</option>
                                <option value="luxury">Luxury</option>
                            </select>
                        </div>
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
                    <div style="display:flex;justify-content:flex-end;"><button type="submit" class="btn btn-primary">Create &amp; Open</button></div>
                </form>
            </div>

            <div class="card" id="csv-import-panel" style="margin-bottom:16px;">
                <h3 style="margin-bottom:12px;">CSV Import</h3>
                <form method="POST" action="{{ url('/accommodations/import-csv') }}" enctype="multipart/form-data">
                    @csrf
                    @include('partials.company-selector')
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:12px;">Upload columns: <strong>name, chain, location</strong></p>
                    <div class="form-row cols-2">
                        <div class="form-group"><label>CSV File *</label><input type="file" name="csv_file" accept=".csv,.txt" required></div>
                        <div style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-outline">Import</button></div>
                    </div>
                </form>
            </div>

            {{-- Server-side filter bar --}}
            <form method="GET" action="{{ url('/accommodations') }}" id="filterForm">
                <div class="filter-bar">
                    <input type="text" name="search" class="search-input" placeholder="Search by name..." value="{{ request('search') }}">
                    <select name="location" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Locations</option>
                        @foreach($destinations as $d)<option value="{{ $d->id }}" {{ request('location') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                    </select>
                    <select name="country_id" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)<option value="{{ $country->id }}" {{ (string) request('country_id') === (string) $country->id ? 'selected' : '' }}>{{ $country->name }}</option>@endforeach
                    </select>
                    <select name="region_id" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Regions</option>
                        @foreach($regions as $region)<option value="{{ $region->id }}" {{ (string) request('region_id') === (string) $region->id ? 'selected' : '' }}>{{ $region->name }}</option>@endforeach
                    </select>
                    <select name="chain" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Chains</option>
                        @foreach($chainsQuery as $ch)<option value="{{ $ch }}" {{ request('chain') == $ch ? 'selected' : '' }}>{{ $ch }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    @if(request()->hasAny(['search','location','country_id','region_id','chain','letter']))
                        <a href="{{ url('/accommodations') }}" class="btn btn-outline btn-sm">Clear</a>
                    @endif
                </div>

                {{-- Alphabet bar --}}
                <div class="alpha-bar">
                    <a href="{{ url('/accommodations') . '?' . http_build_query(request()->except('letter','page')) }}"
                       class="{{ !request('letter') ? 'active' : '' }}">All</a>
                    @foreach(range('A','Z') as $letter)
                        <a href="{{ url('/accommodations') . '?' . http_build_query(array_merge(request()->except('page'), ['letter' => $letter])) }}"
                           class="{{ request('letter') === $letter ? 'active' : '' }}">{{ $letter }}</a>
                    @endforeach
                </div>
            </form>

            @if($accommodations->count())
            <div class="card card-flush">
                @foreach($accommodations as $idx => $a)
                <div class="acc-row">
                    <span class="acc-num">{{ $accommodations->firstItem() + $idx }}</span>
                    <div class="acc-thumb">
                        @php
                            $cover = $a->accommodationMedia->firstWhere('is_cover', true) ?? $a->accommodationMedia->first();
                        @endphp
                        @if($cover)
                            <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $a->name }}">
                        @else
                            <span class="placeholder-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M2 20h20"/></svg>
                            </span>
                        @endif
                    </div>
                    <div class="acc-info">
                        <div class="acc-name">{{ $a->name }}</div>
                        <div class="acc-meta">
                            <span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $a->location?->name ?? '—' }}
                            </span>
                            <span>{{ $a->location?->countryRef?->name ?? '—' }}</span>
                            <span>{{ $a->location?->regionRef?->name ?? '—' }}</span>
                            @if($a->chain)<span>{{ $a->chain }}</span>@endif
                        </div>
                    </div>
                    <div class="acc-col">
                        <span class="cat-tag cat-{{ $a->category }}">{{ ucfirst($a->category) }}</span>
                    </div>
                    <div class="acc-col">
                        <span class="status-dot {{ $a->is_active ? 'active' : 'inactive' }}"></span>
                        <span style="font-size:12px;color:var(--text-muted);">{{ $a->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="action-cell">
                        <a href="{{ url('/accommodations/' . $a->id) }}" class="act-open">View</a>
                        <a href="{{ url('/accommodations/' . $a->id . '/manage') }}" class="act-open">Manage</a>
                        <a href="{{ url('/accommodations/' . $a->id . '/manage') }}#tab-rates" class="act-open">Rates</a>
                        <a href="{{ url('/accommodations/' . $a->id . '/edit') }}" class="act-open">Edit</a>
                        <form method="POST" action="{{ url('/accommodations/' . $a->id) }}" onsubmit="return confirm('Delete {{ addslashes($a->name) }}?')" class="delete-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-delete">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="pagination-wrap">{{ $accommodations->links() }}</div>
            @else
            <div class="card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.4"><path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M2 20h20"/></svg>
                    </div>
                    <p>No accommodation found</p>
                    <a class="btn btn-primary" style="margin-top:16px;" href="#add-accommodation-panel">+ Add Your First Accommodation</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
