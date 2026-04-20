@extends('layouts.app')
@section('title', ($hotel->name ?? 'New') . ' — Accommodation — Itinex')
@section('styles')
<style>
    .detail-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
    .detail-header h2 { font-size:22px; font-weight:700; }
    .detail-header .back-link { font-size:13px; color:var(--text-secondary); text-decoration:none; }
    .detail-header .back-link:hover { color:var(--color-primary); }
    .tab-container { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden; }
    .tab-body { padding:24px; }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .info-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
    @media(max-width:768px) { .info-grid, .info-grid-3 { grid-template-columns:1fr; } }
    .mini-table { width:100%; border-collapse:collapse; font-size:13px; }
    .mini-table th { text-align:left; padding:8px 12px; background:var(--bg-table-head); border-bottom:1px solid var(--border-color); font-weight:600; color:var(--text-secondary); font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
    .mini-table td { padding:8px 12px; border-bottom:1px solid var(--border-light); }
    .mini-table tr:last-child td { border-bottom:none; }
    .inline-form { display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; padding:16px; background:var(--bg-table-head); border-radius:var(--radius-sm); margin-bottom:16px; }
    .inline-form .form-group { margin-bottom:0; flex:1; min-width:120px; }
    .inline-form .form-group label { font-size:11px; font-weight:600; color:var(--text-secondary); margin-bottom:4px; display:block; }
    .inline-form .form-group input, .inline-form .form-group select { font-size:12px; padding:6px 10px; }
    .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:12px; }
    .gallery-item { position:relative; border-radius:var(--radius-sm); overflow:hidden; aspect-ratio:4/3; background:#f1f5f9; }
    .gallery-item img { width:100%; height:100%; object-fit:cover; }
    .gallery-item .overlay { position:absolute; inset:0; background:rgba(0,0,0,.4); display:flex; align-items:center; justify-content:center; gap:8px; opacity:0; transition:opacity var(--transition-fast); }
    .gallery-item:hover .overlay { opacity:1; }
    .gallery-item .overlay button, .gallery-item .overlay a { padding:4px 10px; font-size:11px; border-radius:4px; border:none; cursor:pointer; font-weight:600; }
    .cover-badge { position:absolute; top:6px; left:6px; background:var(--color-primary); color:#fff; font-size:10px; padding:2px 8px; border-radius:10px; font-weight:700; }
    .year-btn { padding:6px 16px; font-size:12px; font-weight:600; border:1px solid var(--border-color); background:var(--bg-card); border-radius:var(--radius-sm); cursor:pointer; color:var(--text-secondary); }
    .year-btn.active { background:var(--color-success); color:#fff; border-color:var(--color-success); }
    .section-title { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border-light); }
    .cat-tag { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; }
    .cat-budget { background:var(--color-warning-light); color:#92400e; }
    .cat-midrange { background:var(--color-info-light); color:#1e40af; }
    .cat-luxury { background:#fce7f3; color:#9d174d; }
</style>
@endsection
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'accommodations'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Accommodation</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="detail-header">
                <div>
                    <a href="{{ url('/accommodations') }}" class="back-link">&larr; Back to Accommodation</a>
                    <h2>{{ $hotel->name }}</h2>
                </div>
                <span class="cat-tag cat-{{ $hotel->category }}">{{ $hotel->category }}</span>
            </div>

            <div class="tab-container" id="mainTabs">
                <div class="tab-nav">
                    <button class="active" data-tab="tab-overview">Overview</button>
                    <button data-tab="tab-gallery">Gallery</button>
                    <button data-tab="tab-description">Description</button>
                    <button data-tab="tab-room-cat">Room Categories</button>
                    <button data-tab="tab-room-types">Room Types</button>
                    <button data-tab="tab-meal-plans">Meal Plans</button>
                    <button data-tab="tab-rates">Rates &amp; Policies</button>
                </div>
                <div class="tab-body">

{{-- ═══ Tab 1: Overview ═══ --}}
<div class="tab-content active" id="tab-overview">
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id) }}">
        @csrf @method('PUT')

        <div class="section-title">General Details</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ $hotel->name }}" required></div>
            <div class="form-group">
                <label>Destination *</label>
                <select name="location_id" required>
                    <option value="">Select</option>
                    @foreach($destinations as $d)<option value="{{ $d->id }}" {{ $hotel->location_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    @foreach(['budget','midrange','luxury'] as $c)<option value="{{ $c }}" {{ $hotel->category == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Chain</label><input type="text" name="chain" value="{{ $hotel->chain }}"></div>
        </div>

        <div class="section-title">Contacts</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" value="{{ $hotel->contact_person }}"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ $hotel->phone }}"></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ $hotel->email }}"></div>
            <div class="form-group"><label>Website</label><input type="url" name="website" value="{{ $hotel->website }}"></div>
        </div>

        <div class="section-title">Location</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group"><label>Address</label><textarea name="address" rows="2">{{ $hotel->address }}</textarea></div>
            <div class="info-grid">
                <div class="form-group"><label>Latitude</label><input type="number" name="latitude" step="0.0000001" value="{{ $hotel->latitude }}"></div>
                <div class="form-group"><label>Longitude</label><input type="number" name="longitude" step="0.0000001" value="{{ $hotel->longitude }}"></div>
            </div>
        </div>

        <div class="section-title">VAT &amp; Markup</div>
        <div class="info-grid" style="margin-bottom:24px;">
            <div class="form-group">
                <label>VAT Type</label>
                <select name="vat_type">
                    @foreach(['inclusive','exclusive','exempt'] as $v)<option value="{{ $v }}" {{ $hotel->vat_type == $v ? 'selected' : '' }}>{{ ucfirst($v) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Markup %</label><input type="number" name="markup" step="0.01" value="{{ $hotel->markup }}"></div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active">
                    <option value="1" {{ $hotel->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$hotel->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="sticky-save"><button type="submit" class="btn btn-primary">Save Overview</button></div>
    </form>
</div>

{{-- ═══ Tab 2: Gallery ═══ --}}
<div class="tab-content" id="tab-gallery">
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/media') }}" enctype="multipart/form-data" style="margin-bottom:20px;">
        @csrf
        <div style="display:flex;gap:12px;align-items:flex-end;">
            <div class="form-group" style="flex:1;margin-bottom:0;"><label>Upload Images</label><input type="file" name="images[]" multiple accept="image/*"></div>
            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
        </div>
    </form>
    <div class="gallery-grid">
        @foreach($hotel->accommodationMedia as $m)
        <div class="gallery-item">
            @if($m->is_cover)<div class="cover-badge">Cover</div>@endif
            <img src="{{ asset('storage/' . $m->file_path) }}" alt="">
            <div class="overlay">
                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/media/' . $m->id . '/cover') }}">@csrf @method('PATCH')<button style="background:var(--color-primary);color:#fff;">Cover</button></form>
                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/media/' . $m->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button style="background:#ef4444;color:#fff;">Delete</button></form>
            </div>
        </div>
        @endforeach
    </div>
    @if($hotel->accommodationMedia->isEmpty())
    <div class="empty-state"><div class="empty-icon">&#128247;</div><p>No images yet</p></div>
    @endif
</div>

{{-- ═══ Tab 3: Description ═══ --}}
<div class="tab-content" id="tab-description">
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id) }}">
        @csrf @method('PUT')
        <div class="form-group"><label>Description</label><textarea name="description" rows="12" style="font-size:14px;line-height:1.7;">{{ $hotel->description }}</textarea></div>
        <div class="sticky-save"><button type="submit" class="btn btn-primary">Save Description</button></div>
    </form>
</div>

{{-- ═══ Tab 4: Room Categories ═══ --}}
<div class="tab-content" id="tab-room-cat">
    <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-categories') }}" class="inline-form">
        @csrf
        <div class="form-group"><label>Category Name</label><input type="text" name="name" required placeholder="e.g. Standard"></div>
        <div class="form-group"><label>Description</label><input type="text" name="description" placeholder="Optional"></div>
        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
    </form>
    @if($hotel->roomCategories->count())
    <table class="mini-table">
        <thead><tr><th>Name</th><th>Description</th><th></th></tr></thead>
        <tbody>
            @foreach($hotel->roomCategories as $rc)
            <tr>
                <td style="font-weight:600;">{{ $rc->name }}</td>
                <td>{{ $rc->description ?? '—' }}</td>
                <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-categories/' . $rc->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state"><p>No room categories</p></div>
    @endif
</div>

{{-- ═══ Tab 5: Room Types ═══ --}}
<div class="tab-content" id="tab-room-types">
    <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">Room types are managed via the hotel rates system. Current room types configured:</p>
    @if($hotel->roomTypes->count())
    <table class="mini-table">
        <thead><tr><th>Name</th></tr></thead>
        <tbody>@foreach($hotel->roomTypes as $rt)<tr><td>{{ $rt->name }}</td></tr>@endforeach</tbody>
    </table>
    @else
    <div class="empty-state"><p>No room types configured</p></div>
    @endif
</div>

{{-- ═══ Tab 6: Meal Plans ═══ --}}
<div class="tab-content" id="tab-meal-plans">
    <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">Global meal plans used in room rate configurations:</p>
    @if($mealPlans->count())
    <table class="mini-table">
        <thead><tr><th>Name</th></tr></thead>
        <tbody>@foreach($mealPlans as $mp)<tr><td>{{ $mp->name }}</td></tr>@endforeach</tbody>
    </table>
    @else
    <div class="empty-state"><p>No meal plans configured</p></div>
    @endif
</div>

{{-- ═══ Tab 7: Rates & Policies ═══ --}}
<div class="tab-content" id="tab-rates">
    <div id="ratesSubTabs">
        <div class="sub-tab-nav">
            <button class="active" data-subtab="sub-years">Rate Years</button>
            <button data-subtab="sub-seasons">Seasons</button>
            <button data-subtab="sub-room-rates">Room Rates</button>
            <button data-subtab="sub-supplements">Supplements</button>
            <button data-subtab="sub-child">Child Policy</button>
            <button data-subtab="sub-cancel">Cancellation</button>
            <button data-subtab="sub-payment">Payment</button>
        </div>

        {{-- Sub: Rate Years --}}
        <div data-subtab-content="sub-years" style="display:block;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Year</label><input type="number" name="year" required min="2020" max="2040" value="{{ date('Y') }}"></div>
                <div class="form-group"><label>Label</label><input type="text" name="label" placeholder="e.g. High Season 2026"></div>
                <button type="submit" class="btn btn-primary btn-sm">+ Add Year</button>
            </form>
            @if($hotel->rateYears->count())
            <table class="mini-table">
                <thead><tr><th>Year</th><th>Label</th><th>Status</th><th>Seasons</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($hotel->rateYears as $ry)
                    <tr>
                        <td style="font-weight:700;">{{ $ry->year }}</td>
                        <td>{{ $ry->label ?? '—' }}</td>
                        <td><span class="badge {{ $ry->is_active ? 'badge-green' : 'badge-gray' }}">{{ $ry->is_active ? 'Active' : 'Draft' }}</span></td>
                        <td>{{ $ry->seasons->count() }}</td>
                        <td>
                            <div style="display:flex;gap:4px;">
                                @if(!$ry->is_active)
                                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years/' . $ry->id . '/activate') }}">@csrf @method('PATCH')<button class="btn btn-success btn-xs">Activate</button></form>
                                @endif
                                <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years/' . $ry->id . '/clone') }}">@csrf<button class="btn btn-outline btn-xs">Clone</button></form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No rate years</p></div>
            @endif
        </div>

        {{-- Sub: Seasons --}}
        <div data-subtab-content="sub-seasons" style="display:none;">
            @if($activeYear)
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Seasons for active year: <strong>{{ $activeYear->year }}</strong></p>
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/rate-years/' . $activeYear->id . '/seasons') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. Peak"></div>
                <div class="form-group"><label>Start</label><input type="date" name="start_date" required></div>
                <div class="form-group"><label>End</label><input type="date" name="end_date" required></div>
                <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
            </form>
            @if($activeYear->seasons->count())
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Start</th><th>End</th><th></th></tr></thead>
                <tbody>
                    @foreach($activeYear->seasons as $s)
                    <tr>
                        <td style="font-weight:600;">{{ $s->name }}</td>
                        <td>{{ $s->start_date }}</td>
                        <td>{{ $s->end_date }}</td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/seasons/' . $s->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No seasons yet</p></div>
            @endif
            @else
            <div class="empty-state"><p>Create a rate year first and activate it</p></div>
            @endif
        </div>

        {{-- Sub: Room Rates --}}
        <div data-subtab-content="sub-room-rates" style="display:none;">
            @if($activeYear)
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-rates') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Season</label>
                    <select name="season_id" required><option value="">—</option>@foreach($activeYear->seasons as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Room Cat</label>
                    <select name="room_category_id"><option value="">—</option>@foreach($hotel->roomCategories as $rc)<option value="{{ $rc->id }}">{{ $rc->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Meal Plan</label>
                    <select name="meal_plan_id"><option value="">—</option>@foreach($mealPlans as $mp)<option value="{{ $mp->id }}">{{ $mp->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Adult Rate</label><input type="number" name="adult_rate" step="0.01" required></div>
                <div class="form-group"><label>Child Rate</label><input type="number" name="child_rate" step="0.01" value="0"></div>
                <div class="form-group"><label>Single Supp</label><input type="number" name="single_supplement" step="0.01" value="0"></div>
                <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
            </form>
            @if($roomRates->count())
            <table class="mini-table">
                <thead><tr><th>Season</th><th>Room Cat</th><th>Meal Plan</th><th>Adult</th><th>Child</th><th>Single Supp</th><th></th></tr></thead>
                <tbody>
                    @foreach($roomRates as $rr)
                    <tr>
                        <td>{{ $rr->season?->name ?? '—' }}</td>
                        <td>{{ $rr->roomCategory?->name ?? '—' }}</td>
                        <td>{{ $rr->mealPlan?->name ?? '—' }}</td>
                        <td style="font-weight:600;">${{ number_format($rr->adult_rate, 2) }}</td>
                        <td>${{ number_format($rr->child_rate, 2) }}</td>
                        <td>${{ number_format($rr->single_supplement, 2) }}</td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/room-rates/' . $rr->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No room rates yet</p></div>
            @endif
            @else
            <div class="empty-state"><p>Create a rate year first</p></div>
            @endif
        </div>

        {{-- Sub: Supplements --}}
        <div data-subtab-content="sub-supplements" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/holiday-supplements') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. Christmas"></div>
                <div class="form-group"><label>Start</label><input type="date" name="start_date" required></div>
                <div class="form-group"><label>End</label><input type="date" name="end_date" required></div>
                <div class="form-group"><label>Amount</label><input type="number" name="supplement_amount" step="0.01" required></div>
                <div class="form-group"><label>Type</label>
                    <select name="supplement_type"><option value="flat">Flat</option><option value="percentage">Percentage</option></select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
            </form>
            @if($hotel->holidaySupplements->count())
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Period</th><th>Amount</th><th>Type</th><th></th></tr></thead>
                <tbody>
                    @foreach($hotel->holidaySupplements as $hs)
                    <tr>
                        <td style="font-weight:600;">{{ $hs->name }}</td>
                        <td>{{ $hs->start_date }} → {{ $hs->end_date }}</td>
                        <td>{{ $hs->supplement_type === 'percentage' ? $hs->supplement_amount . '%' : '$' . number_format($hs->supplement_amount, 2) }}</td>
                        <td><span class="badge badge-gray">{{ ucfirst($hs->supplement_type) }}</span></td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/holiday-supplements/' . $hs->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No supplements</p></div>
            @endif
        </div>

        {{-- Sub: Child Policy --}}
        <div data-subtab-content="sub-child" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/child-policies') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Age From</label><input type="number" name="age_from" min="0" max="17" required></div>
                <div class="form-group"><label>Age To</label><input type="number" name="age_to" min="0" max="17" required></div>
                <div class="form-group"><label>Discount %</label><input type="number" name="discount_percentage" step="0.01" required></div>
                <div class="form-group"><label>Policy</label>
                    <select name="policy_type"><option value="free">Free</option><option value="discounted">Discounted</option><option value="full_rate">Full Rate</option></select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
            </form>
            @if($hotel->childPolicies->count())
            <table class="mini-table">
                <thead><tr><th>Age Range</th><th>Policy</th><th>Discount</th><th></th></tr></thead>
                <tbody>
                    @foreach($hotel->childPolicies as $cp)
                    <tr>
                        <td style="font-weight:600;">{{ $cp->age_from }} – {{ $cp->age_to }} yrs</td>
                        <td><span class="badge badge-blue">{{ ucfirst(str_replace('_', ' ', $cp->policy_type)) }}</span></td>
                        <td>{{ $cp->discount_percentage }}%</td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/child-policies/' . $cp->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No child policies</p></div>
            @endif
        </div>

        {{-- Sub: Cancellation --}}
        <div data-subtab-content="sub-cancel" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/cancellation-policies') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Days Before</label><input type="number" name="days_before_arrival" min="0" required></div>
                <div class="form-group"><label>Penalty %</label><input type="number" name="penalty_percentage" step="0.01" required></div>
                <div class="form-group"><label>Description</label><input type="text" name="description"></div>
                <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
            </form>
            @if($hotel->cancellationPolicies->count())
            <table class="mini-table">
                <thead><tr><th>Days Before</th><th>Penalty</th><th>Description</th><th></th></tr></thead>
                <tbody>
                    @foreach($hotel->cancellationPolicies as $cp)
                    <tr>
                        <td style="font-weight:600;">{{ $cp->days_before_arrival }} days</td>
                        <td>{{ $cp->penalty_percentage }}%</td>
                        <td>{{ $cp->description ?? '—' }}</td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/cancellation-policies/' . $cp->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No cancellation policies</p></div>
            @endif
        </div>

        {{-- Sub: Payment --}}
        <div data-subtab-content="sub-payment" style="display:none;">
            <form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/payment-policies') }}" class="inline-form">
                @csrf
                <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="e.g. Deposit"></div>
                <div class="form-group"><label>Percentage</label><input type="number" name="percentage" step="0.01" required></div>
                <div class="form-group"><label>Due (Days Before)</label><input type="number" name="days_before" min="0" required></div>
                <div class="form-group"><label>Description</label><input type="text" name="description"></div>
                <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
            </form>
            @if($hotel->paymentPolicies->count())
            <table class="mini-table">
                <thead><tr><th>Name</th><th>Percentage</th><th>Due</th><th>Description</th><th></th></tr></thead>
                <tbody>
                    @foreach($hotel->paymentPolicies as $pp)
                    <tr>
                        <td style="font-weight:600;">{{ $pp->name }}</td>
                        <td>{{ $pp->percentage }}%</td>
                        <td>{{ $pp->days_before }} days before</td>
                        <td>{{ $pp->description ?? '—' }}</td>
                        <td><form method="POST" action="{{ url('/accommodations/' . $hotel->id . '/payment-policies/' . $pp->id) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state"><p>No payment policies</p></div>
            @endif
        </div>
    </div>
</div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
initTabs('mainTabs');
initSubTabs('ratesSubTabs');
</script>
@endsection
