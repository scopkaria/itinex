@extends('layouts.app')
@section('title', ($hotel->name ?? 'Accommodation') . ' - View - Itinex')
@section('styles')
<style>
    .acv-shell { display: grid; gap: 16px; }
    .acv-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:8px; }
    .acv-title { font-size:26px; font-weight:700; line-height:1.2; }
    .acv-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .acv-grid { display:grid; grid-template-columns: repeat(12, 1fr); gap:16px; }
    .acv-col-8 { grid-column: span 8; }
    .acv-col-4 { grid-column: span 4; }
    .acv-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:16px; padding:16px; }
    .acv-card h3 { margin:0 0 10px; font-size:15px; }
    .acv-kv { display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:13px; }
    .acv-kv div { padding:10px; background:var(--bg-muted); border-radius:10px; }
    .acv-gallery { display:grid; grid-template-columns:repeat(4,minmax(120px,1fr)); gap:10px; }
    .acv-gallery img { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:10px; border:1px solid var(--border-light); }
    .acv-list { display:grid; gap:10px; }
    .acv-pill { display:inline-flex; align-items:center; padding:3px 8px; border-radius:999px; background:#e2e8f0; font-size:11px; font-weight:600; }
    .acv-table { width:100%; border-collapse:collapse; font-size:13px; }
    .acv-table th, .acv-table td { padding:10px; border-bottom:1px solid var(--border-light); text-align:left; }
    @media (max-width: 1024px) {
        .acv-col-8, .acv-col-4 { grid-column: span 12; }
        .acv-gallery { grid-template-columns:repeat(2,minmax(120px,1fr)); }
    }
</style>
@endsection
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'accommodations'])
    <div class="main-content">
        <header class="topbar">
            <h2>Accommodation View</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>

        <div class="content-area acv-shell">
            <div class="acv-head">
                <div>
                    <a href="{{ url('/accommodations') }}" class="back-link">&larr; Back</a>
                    <div class="acv-title">{{ $hotel->name }}</div>
                    <div style="font-size:13px;color:var(--text-muted);">{{ $hotel->location?->name }} · {{ $hotel->location?->countryRef?->name }} · {{ $hotel->location?->regionRef?->name }}</div>
                </div>
                <div class="acv-actions">
                    @if($canManageAccommodation)
                        <a class="btn btn-primary" href="{{ url('/accommodations/' . $hotel->id . '/manage') }}">Manage</a>
                    @endif
                    <a class="btn btn-outline" href="{{ url('/accommodations') }}">List</a>
                </div>
            </div>

            <div class="acv-grid">
                <section class="acv-card acv-col-8">
                    <h3>General Info</h3>
                    <div class="acv-kv">
                        <div><strong>Chain</strong><br>{{ $hotel->chain ?: 'N/A' }}</div>
                        <div><strong>Category</strong><br>{{ ucfirst($hotel->category) }}</div>
                        <div><strong>Contact</strong><br>{{ $hotel->contact_person ?: 'N/A' }}</div>
                        <div><strong>Email</strong><br>{{ $hotel->email ?: 'N/A' }}</div>
                        <div><strong>Phone</strong><br>{{ $hotel->phone ?: 'N/A' }}</div>
                        <div><strong>Website</strong><br>{{ $hotel->website ?: 'N/A' }}</div>
                    </div>
                </section>

                <section class="acv-card acv-col-4">
                    <h3>Room Setup</h3>
                    <div class="acv-list">
                        <div>
                            <div style="font-size:12px;color:var(--text-muted); margin-bottom:6px;">Room Categories</div>
                            @forelse($hotel->roomCategories as $category)
                                <span class="acv-pill">{{ $category->name }}</span>
                            @empty
                                <div style="font-size:12px;color:var(--text-muted);">No categories</div>
                            @endforelse
                        </div>
                        <div>
                            <div style="font-size:12px;color:var(--text-muted); margin-bottom:6px;">Room Types</div>
                            @forelse($hotel->roomTypes as $type)
                                <span class="acv-pill">{{ $type->label ?? ucfirst(str_replace('_', ' ', $type->type)) }} ({{ $type->max_adults ?? 2 }})</span>
                            @empty
                                <div style="font-size:12px;color:var(--text-muted);">No room types</div>
                            @endforelse
                        </div>
                        <div>
                            <div style="font-size:12px;color:var(--text-muted); margin-bottom:6px;">Meal Plans</div>
                            @foreach($mealPlans as $meal)
                                <span class="acv-pill">{{ $meal->abbreviation ?? $meal->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="acv-card acv-col-8">
                    <h3>Description</h3>
                    <div style="font-size:14px; line-height:1.6; white-space:pre-wrap;">{{ $hotel->description ?: 'No description available.' }}</div>
                </section>

                <section class="acv-card acv-col-4">
                    <h3>Pricing Visibility</h3>
                    <div style="font-size:13px;color:var(--text-secondary);">
                        Raw STO rates are hidden unless role access is explicitly granted. Company viewers consume computed-only rates for quoting safety.
                    </div>
                </section>

                <section class="acv-card acv-col-12">
                    <h3>Images</h3>
                    @if($hotel->accommodationMedia->isEmpty())
                        <div style="font-size:13px;color:var(--text-muted);">No images uploaded.</div>
                    @else
                        <div class="acv-gallery">
                            @foreach($hotel->accommodationMedia as $media)
                                <img src="{{ asset('storage/' . $media->file_path) }}" alt="Accommodation image">
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="acv-card acv-col-12">
                    <h3>Rate Table (Read-only)</h3>
                    <table class="acv-table">
                        <thead>
                            <tr>
                                <th>Season</th>
                                <th>Room Type</th>
                                <th>Meal Plan</th>
                                <th>Single Supp.</th>
                                <th>Sharing Double</th>
                                <th>Sharing Twin</th>
                                <th>Triple Adj.</th>
                                <th>Final Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roomRates as $rate)
                                <tr>
                                    <td>{{ $rate->season?->name ?: 'N/A' }}</td>
                                    <td>{{ $rate->roomType?->label ?? ($rate->roomType?->type ?: 'N/A') }}</td>
                                    <td>{{ $rate->mealPlan?->abbreviation ?? ($rate->mealPlan?->name ?: 'N/A') }}</td>
                                    <td>{{ number_format((float) $rate->single_supplement, 2) }}</td>
                                    <td>{{ number_format((float) ($rate->per_person_sharing_double ?? 0), 2) }}</td>
                                    <td>{{ number_format((float) ($rate->per_person_sharing_twin ?? 0), 2) }}</td>
                                    <td>{{ number_format((float) ($rate->triple_adjustment ?? 0), 2) }}</td>
                                    <td>{{ number_format((float) ($rate->derived_rate ?? $rate->adult_rate), 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" style="color:var(--text-muted)">No rates yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
