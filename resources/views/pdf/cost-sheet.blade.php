<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Cost Sheet — {{ $itinerary->client_name }}</title>
<style>
    @page { margin: 35px 45px; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: '{{ $template->font ?? "Helvetica" }}', Helvetica, Arial, sans-serif; color: #1f2937; font-size: 10px; line-height: 1.4; }

    .watermark { position: fixed; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-size: 72px; font-weight: 900; color: rgba(239, 68, 68, 0.06); letter-spacing: 8px; white-space: nowrap; z-index: 0; }

    /* ── Header ─────────────────── */
    .header { display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 10px; border-bottom: 3px solid #ef4444; margin-bottom: 18px; }
    .header-left .doc-title { font-size: 20px; font-weight: 700; color: #ef4444; }
    .header-left .doc-label { font-size: 9px; color: #ef4444; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
    .header-right { text-align: right; font-size: 10px; color: #6b7280; }
    .header-right strong { color: #1f2937; }
    .confidential { display: inline-block; background: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }

    /* ── Info Row ───────────────── */
    .info-row { display: table; width: 100%; margin-bottom: 18px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 14px; }
    .info-cell { display: table-cell; width: 25%; }
    .info-cell .label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; font-weight: 600; }
    .info-cell .value { font-size: 11px; font-weight: 600; color: #1f2937; }

    /* ── Section ────────────────── */
    .section { margin-bottom: 16px; }
    .section-title { font-size: 12px; font-weight: 700; color: #1f2937; padding: 6px 0; border-bottom: 2px solid #e5e7eb; margin-bottom: 6px; }
    .section-title .count { font-weight: 400; color: #9ca3af; font-size: 10px; }

    /* ── Table ──────────────────── */
    table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 4px; }
    thead th { background: #f3f4f6; padding: 6px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
    tbody td { padding: 6px 10px; border-bottom: 1px solid #f3f4f6; }
    .text-right { text-align: right; }
    .mono { font-family: 'Courier New', monospace; font-size: 10px; }
    .subtotal-row td { background: #f9fafb; font-weight: 700; border-top: 1px solid #e5e7eb; }

    /* ── Grand Totals ──────────── */
    .grand-totals { background: #1f2937; color: #fff; border-radius: 8px; padding: 18px 20px; margin-top: 16px; }
    .grand-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 11px; }
    .grand-row.highlight { font-size: 15px; font-weight: 700; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 10px; margin-top: 6px; }
    .grand-row .label-muted { color: #9ca3af; }
    .profit { color: #34d399; }
    .loss { color: #f87171; }
    .low { color: #fbbf24; }

    /* ── Footer ─────────────────── */
    .pdf-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 45px; font-size: 8px; color: #d1d5db; text-align: center; border-top: 1px solid #f3f4f6; }
</style>
</head>
<body>

<div class="watermark">CONFIDENTIAL</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- HEADER                                         --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="header">
    <div class="header-left">
        <div class="doc-label">Internal Document</div>
        <div class="doc-title">Cost Sheet</div>
    </div>
    <div class="header-right">
        <span class="confidential">Confidential</span><br>
        <strong>{{ $company->name }}</strong><br>
        Ref: CS-{{ str_pad($itinerary->id, 4, '0', STR_PAD_LEFT) }}<br>
        Generated: {{ now()->format('M d, Y H:i') }}
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- TRIP INFO                                      --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="info-row">
    <div class="info-cell">
        <div class="label">Client</div>
        <div class="value">{{ $itinerary->client_name }}</div>
    </div>
    <div class="info-cell">
        <div class="label">Travellers</div>
        <div class="value">{{ $itinerary->number_of_people }} Pax</div>
    </div>
    <div class="info-cell">
        <div class="label">Duration</div>
        <div class="value">{{ $itinerary->total_days }} Days</div>
    </div>
    <div class="info-cell">
        <div class="label">Dates</div>
        <div class="value">{{ $itinerary->start_date->format('M d') }} — {{ $itinerary->end_date->format('M d, Y') }}</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- ACCOMMODATION                                  --}}
{{-- ═══════════════════════════════════════════════ --}}
@if(count($costSheet['breakdown']['accommodation']))
<div class="section">
    <div class="section-title">Accommodation <span class="count">({{ count($costSheet['breakdown']['accommodation']) }} items)</span></div>
    <table>
        <thead>
            <tr><th>Day</th><th>Property</th><th>Room Type</th><th>Meal Plan</th><th class="text-right">Rate</th><th class="text-right">Pax</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @php $accomTotal = 0; @endphp
            @foreach($costSheet['breakdown']['accommodation'] as $a)
            @php $accomTotal += $a['total']; @endphp
            <tr>
                <td>{{ $a['day'] }}</td>
                <td>{{ $a['hotel'] }}</td>
                <td>{{ $a['room_type'] }}</td>
                <td>{{ $a['meal_plan'] }}</td>
                <td class="text-right mono">${{ number_format($a['price_per_person'], 2) }}</td>
                <td class="text-right">{{ $a['people'] }}</td>
                <td class="text-right mono">${{ number_format($a['total'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row"><td colspan="6">Subtotal</td><td class="text-right mono">${{ number_format($accomTotal, 2) }}</td></tr>
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════ --}}
{{-- PARK FEES                                      --}}
{{-- ═══════════════════════════════════════════════ --}}
@if(count($costSheet['breakdown']['park_fees']))
<div class="section">
    <div class="section-title">Park & Conservation Fees <span class="count">({{ count($costSheet['breakdown']['park_fees']) }} items)</span></div>
    <table>
        <thead>
            <tr><th>Day</th><th>Park</th><th>Category</th><th class="text-right">Fee/Person</th><th class="text-right">Pax</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @php $parkTotal = 0; @endphp
            @foreach($costSheet['breakdown']['park_fees'] as $p)
            @php $parkTotal += $p['total']; @endphp
            <tr>
                <td>{{ $p['day'] }}</td>
                <td>{{ $p['park_name'] }}</td>
                <td>{{ $p['resident_type'] }}</td>
                <td class="text-right mono">${{ number_format($p['price_per_person'], 2) }}</td>
                <td class="text-right">{{ $p['people'] }}</td>
                <td class="text-right mono">${{ number_format($p['total'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row"><td colspan="5">Subtotal</td><td class="text-right mono">${{ number_format($parkTotal, 2) }}</td></tr>
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════ --}}
{{-- TRANSPORT                                      --}}
{{-- ═══════════════════════════════════════════════ --}}
@if(count($costSheet['breakdown']['transport']))
<div class="section">
    <div class="section-title">Transport <span class="count">({{ count($costSheet['breakdown']['transport']) }} items)</span></div>
    <table>
        <thead>
            <tr><th>Day</th><th>Vehicle</th><th>Capacity</th><th class="text-right">Cost/Day</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @php $transTotal = 0; @endphp
            @foreach($costSheet['breakdown']['transport'] as $t)
            @php $transTotal += $t['total']; @endphp
            <tr>
                <td>{{ $t['day'] }}</td>
                <td>{{ $t['vehicle'] }}</td>
                <td>{{ $t['capacity'] ?? '-' }} pax</td>
                <td class="text-right mono">${{ number_format($t['price_per_day'], 2) }}</td>
                <td class="text-right mono">${{ number_format($t['total'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row"><td colspan="4">Subtotal</td><td class="text-right mono">${{ number_format($transTotal, 2) }}</td></tr>
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════ --}}
{{-- FLIGHTS                                        --}}
{{-- ═══════════════════════════════════════════════ --}}
@if(count($costSheet['breakdown']['flights']))
<div class="section">
    <div class="section-title">Flights <span class="count">({{ count($costSheet['breakdown']['flights']) }} items)</span></div>
    <table>
        <thead>
            <tr><th>Day</th><th>Flight</th><th>Route</th><th class="text-right">Price/Pax</th><th class="text-right">Pax</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @php $flightTotal = 0; @endphp
            @foreach($costSheet['breakdown']['flights'] as $f)
            @php $flightTotal += $f['total']; @endphp
            <tr>
                <td>{{ $f['day'] }}</td>
                <td>{{ $f['flight'] }}</td>
                <td>{{ $f['origin'] }} &rarr; {{ $f['destination'] }}</td>
                <td class="text-right mono">${{ number_format($f['price_per_person'], 2) }}</td>
                <td class="text-right">{{ $f['people'] }}</td>
                <td class="text-right mono">${{ number_format($f['total'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row"><td colspan="5">Subtotal</td><td class="text-right mono">${{ number_format($flightTotal, 2) }}</td></tr>
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════ --}}
{{-- ACTIVITIES                                     --}}
{{-- ═══════════════════════════════════════════════ --}}
@php
    $activities = collect($costSheet['breakdown']['extras'])->where('type', 'activity')->values();
@endphp
@if($activities->count())
<div class="section">
    <div class="section-title">Activities <span class="count">({{ $activities->count() }} items)</span></div>
    <table>
        <thead>
            <tr><th>Day</th><th>Activity</th><th class="text-right">Cost/Pax</th><th class="text-right">Pax</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @php $actTotal = 0; @endphp
            @foreach($activities as $a)
            @php $actTotal += $a['total']; @endphp
            <tr>
                <td>{{ $a['day'] }}</td>
                <td>{{ $a['name'] }}</td>
                <td class="text-right mono">${{ number_format($a['price_per_person'], 2) }}</td>
                <td class="text-right">{{ $a['people'] }}</td>
                <td class="text-right mono">${{ number_format($a['total'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row"><td colspan="4">Subtotal</td><td class="text-right mono">${{ number_format($actTotal, 2) }}</td></tr>
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════ --}}
{{-- EXTRAS                                         --}}
{{-- ═══════════════════════════════════════════════ --}}
@php
    $extrasOnly = collect($costSheet['breakdown']['extras'])->where('type', 'extra')->values();
@endphp
@if($extrasOnly->count())
<div class="section">
    <div class="section-title">Extras <span class="count">({{ $extrasOnly->count() }} items)</span></div>
    <table>
        <thead>
            <tr><th>Day</th><th>Extra</th><th class="text-right">Cost</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @php $extTotal = 0; @endphp
            @foreach($extrasOnly as $e)
            @php $extTotal += $e['total']; @endphp
            <tr>
                <td>{{ $e['day'] }}</td>
                <td>{{ $e['name'] }}</td>
                <td class="text-right mono">${{ number_format($e['unit_price'], 2) }}</td>
                <td class="text-right mono">${{ number_format($e['total'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row"><td colspan="3">Subtotal</td><td class="text-right mono">${{ number_format($extTotal, 2) }}</td></tr>
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════ --}}
{{-- GRAND TOTALS                                   --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="grand-totals">
    <div class="grand-row"><span class="label-muted">Total Cost (Net)</span><span class="mono">${{ number_format($costSheet['totals']['grand_total'], 2) }}</span></div>
    <div class="grand-row"><span class="label-muted">Markup</span><span>{{ $itinerary->markup_percentage ?? 0 }}%</span></div>
    <div class="grand-row"><span class="label-muted">Selling Price</span><span class="mono">${{ number_format($costSheet['totals']['selling_total'], 2) }}</span></div>
    <div class="grand-row"><span class="label-muted">Per Person Cost</span><span class="mono">${{ number_format($costSheet['totals']['per_person_cost'], 2) }}</span></div>

    @php
        $profit = ($costSheet['totals']['selling_total'] ?? 0) - ($costSheet['totals']['total_cost'] ?? 0);
        $margin = $costSheet['totals']['margin_percentage'] ?? 0;
        $statusClass = $margin > 20 ? 'profit' : ($margin < 0 ? 'loss' : 'low');
    @endphp

    <div class="grand-row highlight">
        <span>Profit</span>
        <span class="{{ $statusClass }} mono">${{ number_format($profit, 2) }}</span>
    </div>
    <div class="grand-row">
        <span class="label-muted">Margin</span>
        <span class="{{ $statusClass }}">{{ number_format($margin, 2) }}%</span>
    </div>
</div>

<div class="pdf-footer">CONFIDENTIAL — {{ $company->name }} — Generated {{ now()->format('M d, Y H:i') }}</div>

</body>
</html>
