@extends('layouts.app')
@section('title', 'Hotels — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'hotels'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Hotels</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="page-header">
                <h2>All Hotels</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').classList.add('open')">+ Add Hotel</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Name</th><th>Destination</th><th>Category</th><th></th></tr></thead>
                        <tbody>
                            @forelse($hotels as $h)
                                <tr>
                                    <td>{{ $h->id }}</td>
                                    <td style="font-weight:600;">{{ $h->name }}</td>
                                    <td>{{ $h->location?->name ?? '—' }}</td>
                                    <td><span class="badge badge-blue">{{ strtoupper($h->category) }}</span></td>
                                    <td>
                                        <form method="POST" action="{{ url('/hotels/' . $h->id) }}" class="delete-form" onsubmit="return confirm('Delete this hotel?')">
                                            @csrf @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">&#127976;</div><p>No hotels yet</p></div></td></tr>
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
        <h3>Add Hotel</h3>
        <form method="POST" action="{{ url('/hotels') }}">
            @csrf
            @include('partials.company-selector')
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group">
                <label>Destination *</label>
                <select name="location_id" required>
                    <option value="">Select destination</option>
                    @foreach($destinations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="budget">Budget</option>
                    <option value="midrange">Midrange</option>
                    <option value="luxury">Luxury</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
