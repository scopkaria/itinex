@extends('layouts.app')
@section('title', 'Park Fees — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'park-fees'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Park Fees</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="page-header">
                <h2>All Park Fees</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').classList.add('open')">+ Add Park Fee</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Park</th><th>Resident Type</th><th>Adult</th><th>Child</th><th></th></tr></thead>
                        <tbody>
                            @forelse($parkFees as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td style="font-weight:600;">{{ $p->park_name }}</td>
                                    <td>{{ $p->resident_type }}</td>
                                    <td>${{ number_format($p->adult_price, 2) }}</td>
                                    <td>${{ number_format($p->child_price, 2) }}</td>
                                    <td>
                                        <form method="POST" action="{{ url('/park-fees/' . $p->id) }}" class="delete-form" onsubmit="return confirm('Delete?')">
                                            @csrf @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">&#127795;</div><p>No park fees yet</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modal">
    <div class="modal">
        <h3>Add Park Fee</h3>
        <form method="POST" action="{{ url('/park-fees') }}">
            @csrf
            @include('partials.company-selector')
            <div class="form-group"><label>Park Name *</label><input type="text" name="park_name" required></div>
            <div class="form-group"><label>Resident Type *</label><input type="text" name="resident_type" placeholder="e.g. non_resident" required></div>
            <div class="form-group"><label>Adult Price ($) *</label><input type="number" name="adult_price" step="0.01" min="0" required></div>
            <div class="form-group"><label>Child Price ($) *</label><input type="number" name="child_price" step="0.01" min="0" required></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
