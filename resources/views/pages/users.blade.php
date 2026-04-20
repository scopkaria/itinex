@extends('layouts.app')
@section('title', 'Users — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'users'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Users</h2>
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
                <h2>All Users</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').classList.add('open')">+ Add User</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Company</th><th>Role</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td>{{ $u->id }}</td>
                                    <td style="font-weight:600;">{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td>{{ $u->company?->name ?? '—' }}</td>
                                    <td>
                                        @if($u->role === 'super_admin')<span class="badge badge-purple">SUPER ADMIN</span>
                                        @elseif($u->role === 'admin')<span class="badge badge-blue">ADMIN</span>
                                        @else<span class="badge badge-amber">STAFF</span>@endif
                                    </td>
                                    <td><span class="badge {{ $u->is_active ? 'badge-green' : 'badge-red' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td>
                                        @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ url('/users/' . $u->id) }}" class="delete-form" onsubmit="return confirm('Delete this user?')">
                                            @csrf @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">&#128101;</div><p>No users yet</p></div></td></tr>
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
        <h3>Add User</h3>
        <form method="POST" action="{{ url('/users') }}">
            @csrf
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password *</label><input type="password" name="password" minlength="6" required></div>
            <div class="form-group">
                <label>Role *</label>
                <select name="role" required>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            @if(auth()->user()->isSuperAdmin() && $companies->count())
            <div class="form-group">
                <label>Company *</label>
                <select name="company_id" required>
                    <option value="">Select company</option>
                    @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            @endif
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
