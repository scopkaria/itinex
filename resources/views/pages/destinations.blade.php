@extends('layouts.app')
@section('title', 'Destinations — Itinex')
@section('styles')
<style>
    .dest-row { display: flex; align-items: center; gap: 16px; padding: 14px 20px; border-bottom: 1px solid var(--border-light); transition: background var(--duration-fast); }
    .dest-row:hover { background: var(--bg-table-hover); }
    .dest-row:last-child { border-bottom: none; }
    .dest-num { width: 36px; font-size: 12px; color: var(--text-muted); font-weight: 600; text-align: center; flex-shrink: 0; }
    .dest-thumb { width: 52px; height: 52px; border-radius: var(--radius-md); background: var(--bg-muted); flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; }
    .dest-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .dest-thumb svg { opacity: .45; }
    .dest-info { flex: 1; min-width: 0; }
    .dest-name { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
    .dest-meta { font-size: 12px; color: var(--text-muted); display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .dest-col { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .dest-stat { text-align: center; padding: 0 12px; }
    .dest-stat-val { font-size: 16px; font-weight: 700; color: var(--text-primary); }
    .dest-stat-label { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; }
</style>
@endsection
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'destinations'])
    <div class="main-content">
        <header class="topbar">
            <h2>Destinations</h2>
            <div class="topbar-user">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="page-header">
                <h2>All Destinations <span class="page-title-count">({{ $destinations->count() }})</span></h2>
                <a href="{{ url('/destinations/create') }}" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Destination
                </a>
            </div>

            @if($destinations->count())
            {{-- Filter bar --}}
            <div class="filter-bar">
                <input type="text" id="destSearch" class="search-input" placeholder="Search destinations by name, country, region..." style="max-width:480px;">
                <select id="destTypeFilter" class="filter-select" onchange="filterDests()">
                    <option value="">All Types</option>
                    <option value="national_park">National Park</option>
                    <option value="conservancy">Conservancy</option>
                    <option value="reserve">Reserve</option>
                    <option value="marine_park">Marine Park</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="card card-flush">
                @foreach($destinations as $idx => $d)
                <div class="dest-row" data-search="{{ strtolower($d->name . ' ' . ($d->countryRef?->name ?? '') . ' ' . ($d->regionRef?->name ?? '') . ' ' . $d->category) }}" data-type="{{ $d->category }}">
                    <span class="dest-num">{{ $idx + 1 }}</span>
                    <div class="dest-thumb">
                        @php
                            $cover = $d->media->firstWhere('is_cover', true) ?? $d->media->first();
                        @endphp
                        @if($cover)
                            <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $d->name }}">
                        @else
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        @endif
                    </div>
                    <div class="dest-info">
                        <div class="dest-name">{{ $d->name }}</div>
                        <div class="dest-meta">
                            <span class="type-tag type-{{ $d->category }}">{{ strtoupper(str_replace('_', ' ', $d->category)) }}</span>
                            @if($d->countryRef)<span>{{ $d->countryRef->name }}</span>@endif
                            @if($d->regionRef)<span>{{ $d->regionRef->name }}</span>@endif
                            @if($d->supplier)<span>{{ $d->supplier }}</span>@endif
                        </div>
                    </div>
                    <div class="dest-stat">
                        <div class="dest-stat-val">{{ $d->fees_count }}</div>
                        <div class="dest-stat-label">Rates</div>
                    </div>
                    <div class="action-cell">
                        <a href="{{ url('/destinations/' . $d->id . '/edit') }}" class="act-open">Open</a>
                        <button type="button" class="act-edit" onclick="openClone({{ $d->id }}, '{{ addslashes($d->name) }}')">Clone</button>
                        <form method="POST" action="{{ url('/destinations/' . $d->id) }}" onsubmit="return confirm('Delete {{ addslashes($d->name) }} and all its rates?')" class="delete-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-delete">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.4"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <p>No destinations yet</p>
                    <a href="{{ url('/destinations/create') }}" class="btn btn-primary" style="margin-top:16px;">+ Add Your First Destination</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Clone Modal --}}
<div class="modal-backdrop" id="cloneModal">
    <div class="modal" style="max-width:420px;">
        <h3>Clone Destination</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">This will duplicate the destination and all its rates.</p>
        <form method="POST" action="{{ url('/destinations/clone') }}">
            @csrf
            <input type="hidden" name="source_id" id="cloneSourceId">
            <div class="form-group">
                <label>Source</label>
                <input type="text" id="cloneSourceName" disabled style="background:var(--bg-muted);">
            </div>
            <div class="form-group">
                <label>New Name *</label>
                <input type="text" name="name" required placeholder="Enter new destination name">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('cloneModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Clone</button>
            </div>
        </form>
    </div>
</div>

<script>
function openClone(id, name) {
    document.getElementById('cloneSourceId').value = id;
    document.getElementById('cloneSourceName').value = name;
    document.getElementById('cloneModal').classList.add('open');
}
function filterDests() {
    const q = (document.getElementById('destSearch')?.value || '').toLowerCase();
    const type = document.getElementById('destTypeFilter')?.value || '';
    document.querySelectorAll('.dest-row').forEach(function(row) {
        const text = row.dataset.search;
        const rowType = row.dataset.type;
        let show = (!q || text.includes(q)) && (!type || rowType === type);
        row.style.display = show ? '' : 'none';
    });
}
document.getElementById('destSearch')?.addEventListener('input', filterDests);
</script>
@endsection
