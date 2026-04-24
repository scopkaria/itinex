@extends('layouts.app')
@section('title', 'Park Fees / Conservation Fees - Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'park-fees'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Park Fees / Conservation Fees</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>

        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="toast toast-error">{{ $errors->first() }}</div>@endif

            <div class="card" style="margin-bottom:16px;">
                <form method="GET" action="{{ url('/park-fees') }}" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;padding:14px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Year</label>
                        <select name="year" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;min-width:140px;">
                            <option value="">All Years</option>
                            @for($y = 2024; $y <= 2031; $y++)
                                <option value="{{ $y }}" {{ (string) ($filters['year'] ?? '') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Season</label>
                        <select name="season" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;min-width:180px;">
                            <option value="">All Seasons</option>
                            @foreach($seasons as $s)
                                <option value="{{ $s }}" {{ ($filters['season'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ url('/park-fees') }}" class="btn" style="background:#e2e8f0;color:#0f172a;border:none;padding:10px 14px;border-radius:8px;text-decoration:none;">Reset</a>
                    <button type="submit" class="btn" style="background:#0f766e;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;">Filter</button>
                    <button type="button" class="btn btn-primary" style="margin-left:auto;" onclick="document.getElementById('modal-create-park-fee').classList.add('open')">+ New Park Fee</button>
                </form>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Park Name</th>
                                <th>Region</th>
                                <th>Season</th>
                                <th>Validity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parkFees as $fee)
                                @php
                                    $detail = [
                                        'id' => $fee->id,
                                        'park_name' => $fee->name ?: ($fee->destination?->name ?? 'N/A'),
                                        'supplier' => $fee->supplier ?: ($fee->destination?->supplier ?? 'N/A'),
                                        'region' => $fee->region ?: ($fee->destination?->region ?? 'N/A'),
                                        'season' => $fee->season_name,
                                        'season_id' => $fee->season_id,
                                        'valid_from' => optional($fee->valid_from)->format('Y-m-d'),
                                        'valid_to' => optional($fee->valid_to)->format('Y-m-d'),
                                        'resident_adult' => (float) $fee->resident_adult,
                                        'resident_child' => (float) $fee->resident_child,
                                        'nr_adult' => (float) $fee->nr_adult,
                                        'nr_child' => (float) $fee->nr_child,
                                        'vehicle_rate' => (float) $fee->vehicle_rate,
                                        'guide_rate' => (float) $fee->guide_rate,
                                        'markup_type' => $fee->markup_type ?? 'percent',
                                        'markup' => (float) $fee->markup,
                                        'vat_type' => $fee->vat_type,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $detail['park_name'] }}</td>
                                    <td>{{ $detail['region'] }}</td>
                                    <td>{{ $detail['season'] }}</td>
                                    <td>{{ $detail['valid_from'] ?: '-' }} to {{ $detail['valid_to'] ?: '-' }}</td>
                                    <td>
                                        <button type="button" class="btn" style="background:#1d4ed8;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;" data-fee='@json($detail)' onclick="openDrawer(this)">View</button>
                                        <form method="POST" action="{{ url('/park-fees/' . $fee->id) }}" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn" style="background:#dc2626;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;" onclick="return confirm('Delete park fee record?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty-state"><p>No park fee records found.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modal-create-park-fee">
    <div class="modal" style="max-width:760px;">
        <h3>Create Park Fee</h3>
        <form method="POST" action="{{ url('/park-fees') }}">
            @csrf
            @include('partials.company-selector')
            <div class="form-group">
                <label>Destination *</label>
                <select name="destination_id" required>
                    <option value="">-- Select Destination --</option>
                    @foreach($destinations as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Park Name *</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Supplier</label><input type="text" name="supplier"></div>
            <div class="form-group"><label>Region</label><input type="text" name="region"></div>
            <div class="form-group"><label>Season *</label><input type="text" name="season_name" required></div>
            <div class="form-group"><label>Season ID</label><input type="number" name="season_id" min="1" max="99"></div>
            <div class="form-group"><label>Valid From</label><input type="date" name="valid_from"></div>
            <div class="form-group"><label>Valid To</label><input type="date" name="valid_to"></div>
            <div class="form-group"><label>Resident Adult *</label><input type="number" name="resident_adult" step="0.01" min="0" required></div>
            <div class="form-group"><label>Resident Child *</label><input type="number" name="resident_child" step="0.01" min="0" required></div>
            <div class="form-group"><label>Non-Resident Adult *</label><input type="number" name="nr_adult" step="0.01" min="0" required></div>
            <div class="form-group"><label>Non-Resident Child *</label><input type="number" name="nr_child" step="0.01" min="0" required></div>
            <div class="form-group"><label>Vehicle Fee *</label><input type="number" name="vehicle_rate" step="0.01" min="0" required></div>
            <div class="form-group"><label>Guide Fee *</label><input type="number" name="guide_rate" step="0.01" min="0" required></div>
            <div class="form-group"><label>Markup Type *</label>
                <select name="markup_type" required>
                    <option value="percent">percent</option>
                    <option value="fixed">fixed</option>
                </select>
            </div>
            <div class="form-group"><label>Markup *</label><input type="number" name="markup" step="0.01" min="0" required></div>
            <div class="form-group"><label>VAT Type *</label>
                <select name="vat_type" required>
                    <option value="inclusive">inclusive</option>
                    <option value="exclusive">exclusive</option>
                    <option value="exempted">exempted</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal-create-park-fee').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<div id="parkFeeDrawer" style="position:fixed;top:0;right:-420px;width:420px;max-width:100%;height:100vh;background:#fff;border-left:1px solid #e2e8f0;box-shadow:-4px 0 16px rgba(15,23,42,.15);z-index:1200;transition:right .2s ease;overflow:auto;">
    <div style="padding:16px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:18px;">Park Fee Details</h3>
        <button type="button" style="border:none;background:none;font-size:22px;cursor:pointer;" onclick="closeDrawer()">&times;</button>
    </div>
    <div style="padding:16px;">
        <p><strong>Park:</strong> <span id="df_name"></span></p>
        <p><strong>Supplier:</strong> <span id="df_supplier"></span></p>
        <p><strong>Region:</strong> <span id="df_region"></span></p>
        <p><strong>Season:</strong> <span id="df_season"></span></p>
        <p><strong>Validity:</strong> <span id="df_validity"></span></p>
        <hr style="margin:12px 0;">
        <h4 style="margin:0 0 8px;">Rates Breakdown</h4>
        <p>Resident Adult: <strong id="df_res_adult"></strong></p>
        <p>Resident Child: <strong id="df_res_child"></strong></p>
        <p>Non-Resident Adult: <strong id="df_nr_adult"></strong></p>
        <p>Non-Resident Child: <strong id="df_nr_child"></strong></p>
        <p>Vehicle Fee: <strong id="df_vehicle"></strong></p>
        <p>Guide Fee: <strong id="df_guide"></strong></p>
        <p>Markup: <strong id="df_markup"></strong></p>
        <p>VAT: <strong id="df_vat"></strong></p>
    </div>
</div>

<script>
function formatMoney(value) {
    return '$' + Number(value || 0).toFixed(2);
}

function openDrawer(btn) {
    const data = JSON.parse(btn.getAttribute('data-fee'));
    document.getElementById('df_name').textContent = data.park_name;
    document.getElementById('df_supplier').textContent = data.supplier || '-';
    document.getElementById('df_region').textContent = data.region || '-';
    document.getElementById('df_season').textContent = data.season + (data.season_id ? ' (ID ' + data.season_id + ')' : '');
    document.getElementById('df_validity').textContent = (data.valid_from || '-') + ' to ' + (data.valid_to || '-');
    document.getElementById('df_res_adult').textContent = formatMoney(data.resident_adult);
    document.getElementById('df_res_child').textContent = formatMoney(data.resident_child);
    document.getElementById('df_nr_adult').textContent = formatMoney(data.nr_adult);
    document.getElementById('df_nr_child').textContent = formatMoney(data.nr_child);
    document.getElementById('df_vehicle').textContent = formatMoney(data.vehicle_rate);
    document.getElementById('df_guide').textContent = formatMoney(data.guide_rate);
    document.getElementById('df_markup').textContent = data.markup_type === 'fixed' ? formatMoney(data.markup) : (Number(data.markup).toFixed(2) + '%');
    document.getElementById('df_vat').textContent = data.vat_type;
    document.getElementById('parkFeeDrawer').style.right = '0';
}

function closeDrawer() {
    document.getElementById('parkFeeDrawer').style.right = '-420px';
}
</script>
@endsection
