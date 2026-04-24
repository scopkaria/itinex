@extends('layouts.app')
@section('title', 'Monitors — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'users'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Monitors</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="toast toast-error">{{ session('error') }}</div>@endif

            <div class="page-header">
                <h2>System Monitors</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').classList.add('open')">+ Add Monitor</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td>{{ $u->id }}</td>
                                    <td style="font-weight:600;">{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
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
                                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">&#128101;</div><p>No monitors yet</p></div></td></tr>
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
        <h3>Add Monitor</h3>
        <form method="POST" action="{{ url('/users') }}">
            @csrf
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password *</label><input type="password" name="password" minlength="6" required></div>
            <div class="form-group">
                <label>Role *</label>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Monitor</button>
            </div>
        </form>
    </div>
</div>
@endsection
