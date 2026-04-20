@extends('layouts.app')
@section('title', 'Companies — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'companies'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Companies</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="page-header">
                <h2>All Companies</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').classList.add('open')">+ Add Company</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Users</th><th>Data</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($companies as $c)
                                <tr>
                                    <td>{{ $c->id }}</td>
                                    <td><a href="{{ url('/companies/' . $c->id) }}" style="font-weight:600;color:#4f46e5;">{{ $c->name }}</a></td>
                                    <td>{{ $c->email ?? '—' }}</td>
                                    <td>{{ $c->phone ?? '—' }}</td>
                                    <td>{{ $c->users_count }}</td>
                                    <td style="font-size:12px;color:#6b7280;">{{ $c->destinations_count ?? 0 }} dest · {{ $c->hotels_count ?? 0 }} hotels · {{ $c->itineraries_count ?? 0 }} itin</td>
                                    <td><span class="badge {{ $c->is_active ? 'badge-green' : 'badge-red' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td style="display:flex;gap:8px;align-items:center;">
                                        <a href="{{ url('/companies/' . $c->id) }}" style="color:#4f46e5;font-size:13px;font-weight:500;">View</a>
                                        <form method="POST" action="{{ url('/companies/' . $c->id) }}" class="delete-form" onsubmit="return confirm('Delete this company?')">
                                            @csrf @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">&#9881;</div><p>No companies yet</p></div></td></tr>
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
        <h3>Add Company</h3>
        <form method="POST" action="{{ url('/companies') }}">
            @csrf
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
