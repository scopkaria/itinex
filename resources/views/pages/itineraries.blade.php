@extends('layouts.app')
@section('title', 'Itineraries — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'itineraries'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Itineraries</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="page-header">
                <h2>All Itineraries</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').classList.add('open')">+ New Itinerary</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Client</th><th>Title</th><th>People</th><th>Days</th><th>Cost</th><th>Price</th><th>Profit</th><th>Margin</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($itineraries as $it)
                                @php
                                    $profit = $it->total_price - $it->total_cost;
                                    $margin = (float) $it->margin_percentage;
                                    $status = $it->profitStatus();
                                    $statusColor = match($status) { 'profit' => 'badge-green', 'low' => 'badge-amber', 'loss' => 'badge-red', default => '' };
                                @endphp
                                <tr>
                                    <td>{{ $it->id }}</td>
                                    <td style="font-weight:600;">
                                        <a href="{{ url('/itineraries/' . $it->id) }}" style="color:#4f46e5;text-decoration:underline;">{{ $it->client_name }}</a>
                                    </td>
                                    <td>{{ $it->title ?? '—' }}</td>
                                    <td>{{ $it->number_of_people }}</td>
                                    <td>{{ $it->total_days }}</td>
                                    <td>${{ number_format($it->total_cost, 2) }}</td>
                                    <td>${{ number_format($it->total_price, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $profit >= 0 ? 'badge-green' : 'badge-red' }}">${{ number_format($profit, 2) }}</span>
                                    </td>
                                    <td>{{ number_format($margin, 1) }}%</td>
                                    <td><span class="badge {{ $statusColor }}">{{ strtoupper($status) }}</span></td>
                                    <td>
                                        <a href="{{ url('/itineraries/' . $it->id) }}" style="color:#4f46e5;font-size:13px;font-weight:500;">View</a>
                                        <form method="POST" action="{{ url('/itineraries/' . $it->id) }}" class="delete-form" onsubmit="return confirm('Delete this itinerary and all its items?')" style="display:inline;margin-left:8px;">
                                            @csrf @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="11"><div class="empty-state"><div class="empty-icon">&#128203;</div><p>No itineraries yet. Create your first one!</p></div></td></tr>
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
        <h3>New Itinerary</h3>
        <form method="POST" action="{{ url('/itineraries') }}">
            @csrf
            @include('partials.company-selector')
            <div class="form-group"><label>Client Name *</label><input type="text" name="client_name" required></div>
            <div class="form-group"><label>Title</label><input type="text" name="title" placeholder="e.g. Serengeti Safari"></div>
            <div class="form-group"><label>Number of People *</label><input type="number" name="number_of_people" min="1" value="2" required></div>
            <div class="form-group"><label>Start Date *</label><input type="date" name="start_date" required></div>
            <div class="form-group"><label>End Date *</label><input type="date" name="end_date" required></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
