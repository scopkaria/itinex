@extends('layouts.app')
@section('title', 'Destinations — Itinex')
@section('body')
<style>
    .dest-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; }
    .dest-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0; overflow: hidden; transition: box-shadow .2s; }
    .dest-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }
    .dest-card-body { padding: 20px 24px 16px; }
    .dest-card-name { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .dest-card-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .dest-card-meta span { font-size: 12px; color: #6b7280; }
    .dest-card-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 14px; }
    .dest-card-detail .detail-item { font-size: 12px; }
    .dest-card-detail .detail-label { color: #9ca3af; font-weight: 500; }
    .dest-card-detail .detail-value { color: #374151; font-weight: 600; }
    .dest-card-actions { display: flex; gap: 0; border-top: 1px solid #f3f4f6; }
    .dest-card-actions a,
    .dest-card-actions button { flex: 1; padding: 10px; text-align: center; font-size: 12px; font-weight: 600; border: none; background: none; cursor: pointer; transition: background .15s; color: #6b7280; text-decoration: none; }
    .dest-card-actions a:hover,
    .dest-card-actions button:hover { background: #f9fafb; }
    .dest-card-actions .act-view { color: #4f46e5; }
    .dest-card-actions .act-edit { color: #059669; }
    .dest-card-actions .act-clone { color: #d97706; }
    .dest-card-actions .act-delete { color: #ef4444; }
    .dest-card-actions .act-sep { width: 1px; background: #f3f4f6; }
    .type-tag { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .type-national_park { background: #dcfce7; color: #166534; }
    .type-conservancy { background: #fef3c7; color: #92400e; }
    .type-reserve { background: #dbeafe; color: #1e40af; }
    .type-marine_park { background: #e0e7ff; color: #4338ca; }
    .type-other { background: #f3f4f6; color: #4b5563; }
</style>
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'destinations'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Destinations</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="page-header">
                <h2>All Destinations <span style="color:#9ca3af;font-weight:400;font-size:14px;">({{ $destinations->count() }})</span></h2>
                <a href="{{ url('/destinations/create') }}" class="btn btn-primary">+ Add Destination</a>
            </div>

            @if($destinations->count())
            <div style="margin-bottom:16px;">
                <input type="text" id="destSearch" placeholder="Search destinations by name, category, country, region…" style="width:100%;max-width:480px;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            </div>
            <div class="dest-grid">
                @foreach($destinations as $d)
                <div class="dest-card">
                    <div class="dest-card-body">
                        <div class="dest-card-name">{{ $d->name }}</div>
                        <div class="dest-card-meta">
                            <span class="type-tag type-{{ $d->category }}">{{ strtoupper(str_replace('_', ' ', $d->category)) }}</span>
                            @if($d->countryRef)<span>{{ $d->countryRef->name }}</span>@endif
                        </div>
                        <div class="dest-card-detail">
                            <div class="detail-item">
                                <div class="detail-label">Supplier</div>
                                <div class="detail-value">{{ $d->supplier ?? '—' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Region</div>
                                <div class="detail-value">{{ $d->regionRef?->name ?? '—' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Rates</div>
                                <div class="detail-value">{{ $d->fees_count }} {{ Str::plural('rate', $d->fees_count) }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value" style="word-break:break-all;">{{ $d->email ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="dest-card-actions">
                        <a href="{{ url('/destinations/' . $d->id . '/edit') }}" class="act-view">&#128065; View</a>
                        <div class="act-sep"></div>
                        <a href="{{ url('/destinations/' . $d->id . '/edit') }}" class="act-edit">&#9998; Edit</a>
                        <div class="act-sep"></div>
                        <button type="button" class="act-clone" onclick="openClone({{ $d->id }}, '{{ addslashes($d->name) }}')">&#128203; Clone</button>
                        <div class="act-sep"></div>
                        <form method="POST" action="{{ url('/destinations/' . $d->id) }}" onsubmit="return confirm('Delete {{ addslashes($d->name) }} and all its rates?')" style="flex:1;display:flex;">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-delete" style="flex:1;">&#128465; Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="card">
                <div class="empty-state">
                    <div class="empty-icon">&#127961;</div>
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
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">This will duplicate the destination and all its rates.</p>
        <form method="POST" action="{{ url('/destinations/clone') }}">
            @csrf
            <input type="hidden" name="source_id" id="cloneSourceId">
            <div class="form-group">
                <label>Source</label>
                <input type="text" id="cloneSourceName" disabled style="background:#f9fafb;">
            </div>
            <div class="form-group">
                <label>New Name *</label>
                <input type="text" name="name" required placeholder="Enter new destination name">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('cloneModal').classList.remove('open')">Cancel</button>
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
document.getElementById('destSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.dest-card').forEach(function(card) {
        card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
