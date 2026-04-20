@extends('layouts.app')
@section('title', 'Dashboard — Itinex')

@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'dashboard'])

    {{-- Main --}}
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Dashboard</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </header>

        <div class="content-area">
            {{-- Stats --}}
            <div class="stats-grid">
                <a href="{{ url('/destinations') }}" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-icon" style="background:#eef2ff;color:#4f46e5;">&#127961;</div>
                    <div class="stat-label">Destinations</div>
                    <div class="stat-value">{{ $stats['destinations'] }}</div>
                </a>
                <a href="{{ url('/hotels') }}" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;">&#127976;</div>
                    <div class="stat-label">Hotels</div>
                    <div class="stat-value">{{ $stats['hotels'] }}</div>
                </a>
                <a href="{{ url('/vehicles') }}" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;">&#128663;</div>
                    <div class="stat-label">Vehicles</div>
                    <div class="stat-value">{{ $stats['vehicles'] }}</div>
                </a>
                <a href="{{ url('/itineraries') }}" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-icon" style="background:#fee2e2;color:#dc2626;">&#128203;</div>
                    <div class="stat-label">Itineraries</div>
                    <div class="stat-value">{{ $stats['itineraries'] }}</div>
                </a>
            </div>

            @if(auth()->user()->isSuperAdmin())
                {{-- Companies table --}}
                <div class="card" style="margin-bottom:24px;">
                    <div class="card-header">Companies</div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Users</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($companies as $company)
                                    <tr>
                                        <td>{{ $company->id }}</td>
                                        <td style="font-weight:600;">{{ $company->name }}</td>
                                        <td>{{ $company->email }}</td>
                                        <td>{{ $company->users_count }}</td>
                                        <td><span class="badge {{ $company->is_active ? 'badge-green' : 'badge-red' }}">{{ $company->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;">No companies yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- All users table --}}
                <div class="card">
                    <div class="card-header">All Users</div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td style="font-weight:600;">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->company?->name ?? '—' }}</td>
                                        <td>
                                            @if($user->role === 'super_admin')
                                                <span class="badge badge-purple">SUPER ADMIN</span>
                                            @elseif($user->role === 'admin')
                                                <span class="badge badge-blue">ADMIN</span>
                                            @else
                                                <span class="badge badge-amber">STAFF</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" style="text-align:center;color:#9ca3af;">No users</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                {{-- Company-scoped view for admin/staff --}}
                <div class="card">
                    <div class="card-header">Your Company: {{ auth()->user()->company?->name ?? 'N/A' }}</div>
                    <p style="color:#6b7280;font-size:14px;">Welcome to your Itinex workspace. Use the API to manage master data and itineraries.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
