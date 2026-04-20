@extends('layouts.app')
@section('title', 'Extras — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'extras'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Extras</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="page-header">
                <h2>All Extras</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').classList.add('open')">+ Add Extra</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Name</th><th>Price</th><th></th></tr></thead>
                        <tbody>
                            @forelse($extras as $e)
                                <tr>
                                    <td>{{ $e->id }}</td>
                                    <td style="font-weight:600;">{{ $e->name }}</td>
                                    <td>${{ number_format($e->price, 2) }}</td>
                                    <td>
                                        <form method="POST" action="{{ url('/extras/' . $e->id) }}" class="delete-form" onsubmit="return confirm('Delete?')">
                                            @csrf @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">&#127873;</div><p>No extras yet</p></div></td></tr>
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
        <h3>Add Extra</h3>
        <form method="POST" action="{{ url('/extras') }}">
            @csrf
            @include('partials.company-selector')
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Price ($) *</label><input type="number" name="price" step="0.01" min="0" required></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
