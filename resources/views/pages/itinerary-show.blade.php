@extends('layouts.app')
@section('title', $itinerary->client_name . ' - Itinex')
@section('body')
<style>
    .itn-header { display:flex; gap:16px; align-items:center; margin-bottom:24px; flex-wrap:wrap; }
    .itn-back { display:inline-flex; align-items:center; gap:6px; color:var(--color-muted); font-size:13px; text-decoration:none; }
    .itn-back:hover { color:var(--color-primary); }
    .itn-title { font-size:22px; font-weight:800; flex:1; color:var(--color-heading); }
    .itn-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .btn-chip { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:var(--radius-md); font-size:12px; font-weight:600; text-decoration:none; border:none; cursor:pointer; }
    .btn-pdf { color:#fff; }
    .pdf-itn { background:var(--color-primary); }
    .pdf-quot { background:#059669; }
    .pdf-cost { background:#dc2626; }
    .btn-preview { background:#0f172a; color:#fff; }
    .btn-copy { background:#e2e8f0; color:#1e293b; }
    .btn-token { background:#0b6bcb; color:#fff; }
    .btn-revoke { background:#ffe4e6; color:#9f1239; }

    .summary-strip { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin-bottom:24px; }
    .summary-item { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-md); padding:14px 16px; }
    .summary-item .s-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--color-muted); margin-bottom:4px; }
    .summary-item .s-value { font-size:20px; font-weight:800; color:var(--color-heading); }
    .summary-item .s-value.cost { color:#dc2626; }
    .summary-item .s-value.selling { color:#2563eb; }
    .summary-item .s-value.profit-pos { color:#059669; }
    .summary-item .s-value.profit-neg { color:#dc2626; }

    .markup-card { background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%); border:1px solid #ddd6fe; border-radius:var(--radius-lg); padding:20px 24px; margin-bottom:24px; }
    .markup-title { font-size:14px; font-weight:700; color:#7c3aed; margin-bottom:12px; }
    .markup-form { display:flex; gap:12px; align-items:end; flex-wrap:wrap; }
    .markup-input { padding:8px 12px; border:1px solid #c4b5fd; border-radius:var(--radius-md); font-size:14px; font-weight:600; width:100px; background:#fff; }

    .day-card { border:1px solid var(--border-light); border-radius:var(--radius-lg); margin-bottom:16px; overflow:hidden; background:#fff; }
    .day-header { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; background:var(--bg-muted); border-bottom:1px solid var(--border-light); }
    .day-num { font-size:15px; font-weight:700; color:var(--color-heading); }
    .day-date { font-size:13px; font-weight:400; color:var(--color-muted); margin-left:10px; }
    .day-item { display:flex; align-items:center; gap:12px; padding:12px 20px; border-bottom:1px solid var(--border-light); font-size:13px; }
    .day-item:last-child { border-bottom:none; }
    .day-item .di-ref { flex:1; font-weight:600; color:var(--color-heading); }
    .day-item .di-qty { width:50px; text-align:center; color:var(--color-muted); }
    .day-item .di-cost { width:100px; text-align:right; font-weight:700; font-family:var(--font-mono, monospace); }
    .day-empty { padding:16px 20px; color:var(--color-muted); font-size:13px; }
    .btn-remove { background:none; border:1px solid #fecaca; color:#dc2626; padding:4px 10px; border-radius:var(--radius-sm); font-size:11px; font-weight:600; cursor:pointer; }

    .add-panel { background:#fafbfc; border-top:1px solid var(--border-light); padding:16px 20px; }
    .add-grid { display:grid; grid-template-columns:150px 1fr 90px auto; gap:12px; align-items:end; }
    .add-grid select, .add-grid input { padding:8px 10px; border:1px solid #d1d5db; border-radius:var(--radius-md); font-size:13px; }
    .add-grid label { font-size:11px; font-weight:600; color:var(--color-muted); text-transform:uppercase; letter-spacing:.3px; margin-bottom:2px; display:block; }

    .cost-section-title { font-size:16px; font-weight:700; margin:32px 0 16px; color:var(--color-heading); }
    .cost-summary-bar { display:flex; flex-wrap:wrap; gap:16px; padding:16px 20px; background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-lg); margin-bottom:16px; font-size:13px; }
    .cost-table-card { border:1px solid var(--border-light); border-radius:var(--radius-lg); overflow:hidden; margin-bottom:12px; background:#fff; }
    .cost-table-header { padding:12px 20px; font-size:14px; font-weight:700; border-bottom:1px solid var(--border-light); }
    @media (max-width: 900px) { .add-grid { grid-template-columns:1fr; } }
</style>

<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'itineraries'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:18px;font-weight:700;">{{ $itinerary->title ?? $itinerary->client_name }}</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="toast toast-error">{{ $errors->first() }}</div>@endif

            @php
                $profitStatus = $costSheet['totals']['profit_status'];
                $statusColor = match($profitStatus) { 'profit' => '#059669', 'low' => '#d97706', 'loss' => '#dc2626', default => '#6b7280' };
                $statusLabel = strtoupper($profitStatus);
                $markupPct = (float) $itinerary->markup_percentage;
            @endphp

            <div class="itn-header">
                <a href="{{ url('/itineraries') }}" class="itn-back">Back to list</a>
                <div class="itn-title">{{ $itinerary->client_name }}</div>
                <div class="itn-actions">
                    <a href="{{ url('/itineraries/' . $itinerary->id . '/builder') }}" class="btn-chip btn-token">Step Builder</a>
                    <a href="{{ $publicPreviewUrl }}" target="_blank" class="btn-chip btn-preview">Public Preview</a>
                    <button type="button" class="btn-chip btn-copy" onclick="copyShareLink('temporary')">Copy Expiring Link</button>
                    @if($permanentPreviewUrl)
                        <a href="{{ $permanentPreviewUrl }}" target="_blank" class="btn-chip btn-token">Open Permanent Preview</a>
                        <button type="button" class="btn-chip btn-copy" onclick="copyShareLink('permanent')">Copy Permanent Link</button>
                    @endif
                    <form method="POST" action="{{ url('/itineraries/' . $itinerary->id . '/share-token') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-chip btn-token">{{ $permanentPreviewUrl ? 'Regenerate Permanent Link' : 'Generate Permanent Link' }}</button>
                    </form>
                    @if($permanentPreviewUrl)
                        <form method="POST" action="{{ url('/itineraries/' . $itinerary->id . '/share-token') }}" style="display:inline;" onsubmit="return confirm('Revoke permanent link access?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-chip btn-revoke">Revoke Permanent Link</button>
                        </form>
                    @endif
                    <a href="{{ url('/itineraries/' . $itinerary->id . '/pdf/itinerary') }}" class="btn-chip btn-pdf pdf-itn">Itinerary PDF</a>
                    <a href="{{ url('/itineraries/' . $itinerary->id . '/pdf/quotation') }}" class="btn-chip btn-pdf pdf-quot">Quotation PDF</a>
                    <a href="{{ url('/itineraries/' . $itinerary->id . '/pdf/cost-sheet') }}" class="btn-chip btn-pdf pdf-cost">Cost Sheet PDF</a>
                </div>
            </div>

            <div class="summary-strip">
                <div class="summary-item"><div class="s-label">Client</div><div class="s-value" style="font-size:16px;">{{ $itinerary->client_name }}</div></div>
                <div class="summary-item"><div class="s-label">People</div><div class="s-value">{{ $itinerary->number_of_people }}</div></div>
                <div class="summary-item"><div class="s-label">Days</div><div class="s-value">{{ $itinerary->total_days }}</div></div>
                <div class="summary-item"><div class="s-label">Total Cost</div><div class="s-value cost">${{ number_format($costSheet['totals']['grand_total'], 2) }}</div></div>
                <div class="summary-item"><div class="s-label">Selling Price</div><div class="s-value selling">${{ number_format($itinerary->total_price, 2) }}</div></div>
                <div class="summary-item"><div class="s-label">Profit</div><div class="s-value {{ $costSheet['totals']['profit'] >= 0 ? 'profit-pos' : 'profit-neg' }}">${{ number_format($costSheet['totals']['profit'], 2) }}</div></div>
                <div class="summary-item"><div class="s-label">Margin</div><div class="s-value" style="color:{{ $statusColor }};">{{ number_format($costSheet['totals']['margin_percentage'], 1) }}% <span style="font-size:10px;font-weight:700;background:{{ $statusColor }};color:#fff;padding:2px 8px;border-radius:999px;margin-left:4px;">{{ $statusLabel }}</span></div></div>
                <div class="summary-item"><div class="s-label">Per Person</div><div class="s-value">${{ number_format($costSheet['totals']['per_person_cost'], 2) }}</div></div>
            </div>

            <div class="markup-card">
                <div class="markup-title">Markup &amp; Profit Engine</div>
                <form method="POST" action="{{ url('/itineraries/' . $itinerary->id . '/markup') }}" class="markup-form">
                    @csrf
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">MARKUP %</label>
                        <input type="number" name="markup_percentage" value="{{ $markupPct }}" min="0" max="500" step="0.1" required class="markup-input">
                    </div>
                    <button type="submit" class="btn btn-primary" style="font-size:13px;padding:8px 20px;background:#7c3aed;">Apply Markup</button>
                </form>
            </div>

            {{-- Agent / Partner Override --}}
            <div class="markup-card" style="margin-top:12px;" id="override-card">
                <div class="markup-title">Agent / Partner Override</div>
                @php
                    $activeOverride = $itinerary->pricingOverrides()->where('is_active', true)->first();
                @endphp
                <form id="override-form" onsubmit="submitOverride(event)">
                    @csrf
                    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
                        <div>
                            <label style="font-size:11px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">TYPE</label>
                            <select name="partner_type" id="override-partner-type" class="markup-input" style="width:110px;">
                                <option value="agent" {{ $activeOverride?->partner_type === 'agent' ? 'selected' : '' }}>Agent</option>
                                <option value="partner" {{ $activeOverride?->partner_type === 'partner' ? 'selected' : '' }}>Partner</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">KEY / REF</label>
                            <input type="text" name="partner_key" id="override-partner-key" class="markup-input" style="width:130px;" placeholder="e.g. AGT-001" value="{{ $activeOverride?->partner_key ?? '' }}" required>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">MODE</label>
                            <select name="override_mode" id="override-mode" class="markup-input" style="width:100px;">
                                <option value="percent" {{ $activeOverride?->override_mode === 'percent' ? 'selected' : '' }}>Percent</option>
                                <option value="fixed" {{ $activeOverride?->override_mode === 'fixed' ? 'selected' : '' }}>Fixed $</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">VALUE</label>
                            <input type="number" name="override_value" id="override-value" class="markup-input" style="width:100px;" step="0.01" min="0" value="{{ $activeOverride?->override_value ?? '' }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="font-size:13px;padding:8px 18px;background:#0891b2;">Save Override</button>
                        @if($activeOverride)
                        <button type="button" onclick="deleteOverride('{{ $activeOverride->partner_type }}','{{ $activeOverride->partner_key }}')" class="btn" style="font-size:13px;padding:8px 18px;background:#dc2626;color:#fff;border:none;border-radius:6px;cursor:pointer;">Remove</button>
                        @endif
                    </div>
                    @if($activeOverride)
                    <div style="margin-top:8px;font-size:12px;color:#059669;">
                        Active: <strong>{{ ucfirst($activeOverride->partner_type) }} {{ $activeOverride->partner_key }}</strong>
                        — {{ $activeOverride->override_mode === 'percent' ? $activeOverride->override_value.'%' : '$'.number_format($activeOverride->override_value,2) }} discount
                    </div>
                    @endif
                </form>
            </div>
            <script>
            function submitOverride(e) {
                e.preventDefault();
                const form = document.getElementById('override-form');
                const data = {
                    partner_type: form.querySelector('[name=partner_type]').value,
                    partner_key:  form.querySelector('[name=partner_key]').value,
                    override_mode: form.querySelector('[name=override_mode]').value,
                    override_value: form.querySelector('[name=override_value]').value,
                    _token: form.querySelector('[name=_token]').value,
                };
                fetch('/api/itineraries/{{ $itinerary->id }}/partner-overrides', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':data._token,'Accept':'application/json'},
                    body: JSON.stringify(data),
                }).then(r => r.json()).then(() => location.reload()).catch(err => alert('Error: '+err));
            }
            function deleteOverride(partnerType, partnerKey) {
                if (!confirm('Remove this override?')) return;
                const token = document.querySelector('#override-form [name=_token]').value;
                fetch('/api/itineraries/{{ $itinerary->id }}/partner-overrides?partner_type='+partnerType+'&partner_key='+encodeURIComponent(partnerKey), {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN':token,'Accept':'application/json'},
                }).then(() => location.reload());
            }
            </script>

            <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--color-heading);">Day-by-Day Builder</h3>

            @foreach($itinerary->days as $day)
            <div class="day-card">
                <div class="day-header">
                    <span>
                        <span class="day-num">Day {{ $day->day_number }}</span>
                        @if($day->date)<span class="day-date">{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</span>@endif
                    </span>
                    <button type="button" class="btn btn-primary" style="font-size:12px;padding:5px 14px;" onclick="toggleAddForm({{ $day->id }})">Add Item</button>
                </div>

                @if($day->items->count())
                    @foreach($day->items as $item)
                    @php
                        $ref = $item->reference();
                        $refLabel = match($item->type) {
                            'hotel' => $ref ? ($ref->hotel?->name . ' / ' . $ref->roomType?->type . ' / ' . $ref->mealPlan?->name) : '#' . $item->reference_id,
                            'transport' => $item->reference_source === 'transport_transfer_rate'
                                ? ($ref ? (($ref->route?->originDestination?->name ?? '-') . ' -> ' . ($ref->route?->arrivalDestination?->name ?? '-') . ' / ' . ($ref->vehicleType?->name ?? 'Vehicle Type')) : '#' . $item->reference_id)
                                : ($ref ? $ref->name . ' (' . $ref->capacity . ' pax)' : '#' . $item->reference_id),
                            'park_fee' => $ref ? ($ref->destination?->name . ' - ' . $ref->fee_type . ' / ' . $ref->season_name) : '#' . $item->reference_id,
                            'flight' => $item->reference_source === 'scheduled_flight'
                                ? ($ref ? (($ref->route?->originDestination?->name ?? '-') . ' -> ' . ($ref->route?->arrivalDestination?->name ?? '-') . ' / ' . ($ref->flight_number ?? 'Scheduled')) : '#' . $item->reference_id)
                                : ($ref ? $ref->name . ' (' . ($ref->origin ?? '') . ' to ' . ($ref->destination ?? '') . ')' : '#' . $item->reference_id),
                            'activity' => $ref ? $ref->name : '#' . $item->reference_id,
                            'extra' => in_array($item->reference_source, ['manual_package', 'package'], true)
                                ? ($item->meta['label'] ?? 'Manual Package')
                                : ($ref ? $ref->name : '#' . $item->reference_id),
                            default => '#' . $item->reference_id,
                        };
                        $typeBadge = match($item->type) {
                            'hotel' => 'badge-blue', 'transport' => 'badge-amber', 'park_fee' => 'badge-green',
                            'flight' => 'badge-purple', 'activity' => 'badge-red', 'extra' => 'badge-red', default => '',
                        };
                    @endphp
                    <div class="day-item">
                        <span><span class="badge {{ $typeBadge }}">{{ strtoupper($item->type) }}</span></span>
                        <span class="di-ref">{{ $refLabel }}</span>
                        <span class="di-qty">x {{ $item->quantity }}</span>
                        <span class="di-cost">${{ number_format($item->cost, 2) }}</span>
                        <form method="POST" action="{{ url('/itineraries/' . $itinerary->id . '/items/' . $item->id) }}" onsubmit="return confirm('Remove this item?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-remove">Remove</button>
                        </form>
                    </div>
                    @endforeach
                @else
                    <div class="day-empty">No items yet. Use Add Item to build this day.</div>
                @endif

                <div id="addItemDay{{ $day->id }}" style="display:none;" class="add-panel">
                    <form method="POST" action="{{ url('/itineraries/' . $itinerary->id . '/items') }}">
                        @csrf
                        <input type="hidden" name="itinerary_day_id" value="{{ $day->id }}">
                        <div class="add-grid">
                            <div>
                                <label>Type *</label>
                                <select name="type" required onchange="toggleRefOptions(this, {{ $day->id }})">
                                    <option value="">- Select -</option>
                                    <option value="hotel">Accommodation</option>
                                    <option value="transport">Transport</option>
                                    <option value="park_fee">Destination Fee</option>
                                    <option value="flight">Flight</option>
                                    <option value="activity">Activity</option>
                                    <option value="extra">Extra</option>
                                </select>
                            </div>
                            <div style="position:relative;">
                                <label>Reference *</label>
                                <select class="ref-select ref-hotel-{{ $day->id }}" disabled style="display:none;width:100%;">
                                    <option value="">- Select Hotel Rate -</option>
                                    @foreach($hotelRates as $rate)
                                        <option value="{{ $rate->id }}">{{ $rate->hotel?->name }} - {{ $rate->roomType?->type }} / {{ $rate->mealPlan?->name }} / {{ $rate->season }} (${{ number_format($rate->price_per_person,2) }}/pp)</option>
                                    @endforeach
                                </select>
                                <select class="ref-select ref-transport-{{ $day->id }}" disabled style="display:none;width:100%;">
                                    <option value="">- Select Vehicle -</option>
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }} - {{ $v->capacity }} pax (${{ number_format($v->price_per_day,2) }}/day)</option>
                                    @endforeach
                                </select>
                                <select class="ref-select ref-park_fee-{{ $day->id }}" disabled style="display:none;width:100%;">
                                    <option value="">- Select Destination Fee -</option>
                                    @foreach($destinationFees as $df)
                                        <option value="{{ $df->id }}">{{ $df->destination?->name }} - {{ $df->fee_type }} / {{ $df->season_name }} (${{ number_format($df->nr_adult,2) }}/pp)</option>
                                    @endforeach
                                </select>
                                <select class="ref-select ref-flight-{{ $day->id }}" disabled style="display:none;width:100%;">
                                    <option value="">- Select Flight -</option>
                                    @foreach($flights as $fl)
                                        <option value="{{ $fl->id }}">{{ $fl->name }} - {{ $fl->origin }} to {{ $fl->destination }} (${{ number_format($fl->price_per_person,2) }}/pp)</option>
                                    @endforeach
                                </select>
                                <select class="ref-select ref-activity-{{ $day->id }}" disabled style="display:none;width:100%;">
                                    <option value="">- Select Activity -</option>
                                    @foreach($activities as $act)
                                        <option value="{{ $act->id }}">{{ $act->name }} (${{ number_format($act->price_per_person,2) }}/pp)</option>
                                    @endforeach
                                </select>
                                <select class="ref-select ref-extra-{{ $day->id }}" disabled style="display:none;width:100%;">
                                    <option value="">- Select Extra -</option>
                                    @foreach($extras as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->name }} (${{ number_format($ex->price,2) }})</option>
                                    @endforeach
                                </select>
                                <span class="ref-placeholder-{{ $day->id }}" style="display:block;padding:8px 10px;border:1px solid #d1d5db;border-radius:var(--radius-md);font-size:13px;color:#9ca3af;background:#f3f4f6;">Select type first</span>
                            </div>
                            <div>
                                <label id="qtyLabel{{ $day->id }}">Qty *</label>
                                <input type="number" name="quantity" min="1" value="1" required>
                            </div>
                            <div style="display:flex;gap:6px;padding-bottom:1px;">
                                <button type="submit" class="btn btn-primary" style="font-size:12px;padding:8px 16px;">Add</button>
                                <button type="button" style="font-size:12px;padding:8px 12px;background:none;border:1px solid #d1d5db;border-radius:var(--radius-md);cursor:pointer;color:var(--color-muted);" onclick="toggleAddForm({{ $day->id }})">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach

            <div class="cost-section-title">Cost Sheet Breakdown</div>
            <div class="cost-summary-bar">
                <div>Accommodation: <strong>${{ number_format($costSheet['totals']['accommodation_total'], 2) }}</strong></div>
                <div>Destination Fees: <strong>${{ number_format($costSheet['totals']['park_total'], 2) }}</strong></div>
                <div>Transport: <strong>${{ number_format($costSheet['totals']['transport_total'], 2) }}</strong></div>
                <div>Flights: <strong>${{ number_format($costSheet['totals']['flight_total'], 2) }}</strong></div>
                <div>Extras: <strong>${{ number_format($costSheet['totals']['extras_total'], 2) }}</strong></div>
            </div>

            @if(count($costSheet['breakdown']['accommodation']))
            <div class="cost-table-card"><div class="cost-table-header" style="color:#4f46e5;">Accommodation</div><div class="table-wrap"><table>
                <thead><tr><th>Day</th><th>Hotel</th><th>Room</th><th>Meal</th><th>Season</th><th>$/Person</th><th>Nights</th><th>People</th><th>Cost</th></tr></thead>
                <tbody>@foreach($costSheet['breakdown']['accommodation'] as $row)<tr><td>{{ $row['day'] }}</td><td class="td-name">{{ $row['hotel'] }}</td><td>{{ $row['room_type'] }}</td><td>{{ $row['meal_plan'] }}</td><td><span class="badge badge-amber">{{ strtoupper($row['season']) }}</span></td><td class="td-money">${{ number_format($row['price_per_person'], 2) }}</td><td>{{ $row['nights'] }}</td><td>{{ $row['people'] }}</td><td class="td-money" style="font-weight:700;">${{ number_format($row['total'], 2) }}</td></tr>@endforeach</tbody>
            </table></div></div>
            @endif
        </div>
    </div>
</div>

<script>
const TEMP_SHARE_PREVIEW_URL = @json($publicPreviewUrl);
const PERMANENT_SHARE_PREVIEW_URL = @json($permanentPreviewUrl);
function copyShareLink(kind) {
    var link = kind === 'permanent' && PERMANENT_SHARE_PREVIEW_URL ? PERMANENT_SHARE_PREVIEW_URL : TEMP_SHARE_PREVIEW_URL;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(link).then(function () {
            alert('Share link copied.');
        });
        return;
    }
    var tmp = document.createElement('input');
    tmp.value = link;
    document.body.appendChild(tmp);
    tmp.select();
    document.execCommand('copy');
    document.body.removeChild(tmp);
    alert('Share link copied.');
}

function toggleAddForm(dayId) {
    var el = document.getElementById('addItemDay' + dayId);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function toggleRefOptions(typeSelect, dayId) {
    var type = typeSelect.value;
    var types = ['hotel','transport','park_fee','flight','activity','extra'];
    types.forEach(function(t) {
        var el = document.querySelector('.ref-' + t + '-' + dayId);
        if (el) { el.style.display = 'none'; el.disabled = true; el.removeAttribute('name'); }
    });
    var ph = document.querySelector('.ref-placeholder-' + dayId);
    if (ph) ph.style.display = type ? 'none' : 'block';
    if (type) {
        var active = document.querySelector('.ref-' + type + '-' + dayId);
        if (active) { active.style.display = 'block'; active.disabled = false; active.name = 'reference_id'; }
    }
    var qtyLabel = document.getElementById('qtyLabel' + dayId);
    if (qtyLabel) {
        var hints = { hotel: 'Nights *', transport: 'Days *', park_fee: 'Days *', flight: 'Qty *', activity: 'Qty *', extra: 'Qty *' };
        qtyLabel.textContent = hints[type] || 'Qty *';
    }
}
</script>
@endsection