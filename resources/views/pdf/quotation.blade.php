<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Quotation — {{ $itinerary->client_name }}</title>
<style>
    @page { margin: 40px 50px; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: '{{ $template->font ?? "Helvetica" }}', Helvetica, Arial, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.5; }

    /* ── Header ──────────────────────────────────── */
    .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; border-bottom: 3px solid {{ $template->primary_color ?? '#4f46e5' }}; margin-bottom: 24px; }
    .header-left .company-name { font-size: 20px; font-weight: 700; color: {{ $template->primary_color ?? '#4f46e5' }}; }
    .header-left .company-detail { font-size: 10px; color: #6b7280; }
    .header-right { text-align: right; }
    .header-right .doc-title { font-size: 24px; font-weight: 700; color: #1f2937; letter-spacing: -0.5px; }
    .header-right .doc-meta { font-size: 10px; color: #6b7280; margin-top: 4px; }

    /* ── Client Info ──────────────────────────────── */
    .info-grid { display: table; width: 100%; margin-bottom: 24px; }
    .info-col { display: table-cell; width: 50%; vertical-align: top; }
    .info-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; font-weight: 600; margin-bottom: 2px; }
    .info-value { font-size: 12px; font-weight: 600; color: #1f2937; margin-bottom: 10px; }

    /* ── Table ────────────────────────────────────── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
    thead th { background: {{ $template->primary_color ?? '#4f46e5' }}; color: #fff; padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    thead th:last-child { text-align: right; }
    tbody td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
    tbody td:last-child { text-align: right; font-weight: 600; font-family: monospace; font-size: 10px; }
    .row-image { width: 52px; height: 36px; border-radius: 4px; overflow: hidden; background: #e5e7eb; }
    .row-image img { width: 100%; height: 100%; object-fit: cover; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    .type-badge { display: inline-block; background: #eef2ff; color: {{ $template->primary_color ?? '#4f46e5' }}; padding: 1px 6px; border-radius: 3px; font-size: 9px; font-weight: 600; text-transform: uppercase; }

    /* ── Totals ───────────────────────────────────── */
    .totals-box { float: right; width: 280px; margin-top: 8px; }
    .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 11px; border-bottom: 1px solid #f3f4f6; }
    .totals-row:last-child { border-bottom: none; }
    .totals-row.grand { font-size: 16px; font-weight: 700; color: {{ $template->primary_color ?? '#4f46e5' }}; border-top: 2px solid {{ $template->primary_color ?? '#4f46e5' }}; border-bottom: none; padding-top: 10px; margin-top: 4px; }

    /* ── Notes / Terms ────────────────────────────── */
    .terms { clear: both; padding-top: 30px; font-size: 10px; color: #6b7280; line-height: 1.6; }
    .terms h3 { font-size: 11px; color: #1f2937; font-weight: 700; margin-bottom: 6px; }
    .terms ul { padding-left: 16px; margin-bottom: 12px; }
    .terms li { margin-bottom: 2px; }

    /* ── Footer ───────────────────────────────────── */
    .pdf-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 50px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════ --}}
{{-- HEADER                                         --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="header">
    <div class="header-left">
        @if($template->logo)
        <img src="{{ public_path('storage/' . $template->logo) }}" alt="Logo" style="max-height:40px; margin-bottom:6px; display:block;">
        @endif
        <div class="company-name">{{ $company->name }}</div>
        @if($company->email)<div class="company-detail">{{ $company->email }}</div>@endif
        @if($company->phone)<div class="company-detail">{{ $company->phone }}</div>@endif
        @if($company->address)<div class="company-detail">{{ $company->address }}</div>@endif
    </div>
    <div class="header-right">
        <div class="doc-title">QUOTATION</div>
        <div class="doc-meta">Date: {{ now()->format('M d, Y') }}</div>
        <div class="doc-meta">Ref: QT-{{ str_pad($itinerary->id, 4, '0', STR_PAD_LEFT) }}</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- CLIENT / TRIP INFORMATION                      --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="info-grid">
    <div class="info-col">
        <div class="info-label">Client</div>
        <div class="info-value">{{ $itinerary->client_name }}</div>
        <div class="info-label">Travellers</div>
        <div class="info-value">{{ $itinerary->number_of_people }} {{ $itinerary->number_of_people === 1 ? 'Person' : 'People' }}</div>
    </div>
    <div class="info-col">
        <div class="info-label">Travel Dates</div>
        <div class="info-value">{{ $itinerary->start_date->format('M d, Y') }} — {{ $itinerary->end_date->format('M d, Y') }}</div>
        <div class="info-label">Duration</div>
        <div class="info-value">{{ $itinerary->total_days }} Days / {{ $itinerary->total_days - 1 }} Nights</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- LINE ITEMS                                     --}}
{{-- ═══════════════════════════════════════════════ --}}
<table>
    <thead>
        <tr>
            <th>Day</th>
            <th>Image</th>
            <th>Service</th>
            <th>Description</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($quotationRows as $row)
            <tr>
                <td>Day {{ $row['day_number'] }}</td>
                <td>
                    <div class="row-image">
                        @if($row['image_path'])
                            <img src="{{ public_path('storage/' . $row['image_path']) }}" alt="">
                        @endif
                    </div>
                </td>
                <td><span class="type-badge">{{ $row['type_label'] }}</span></td>
                <td>{{ $row['label'] }}</td>
                <td>${{ number_format($row['amount'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ═══════════════════════════════════════════════ --}}
{{-- TOTALS                                         --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="totals-box">
    <div class="totals-row">
        <span>Subtotal</span>
        <span>${{ number_format($costSheet['totals']['selling_total'], 2) }}</span>
    </div>
    <div class="totals-row">
        <span>Price Per Person</span>
        <span>${{ number_format($costSheet['totals']['per_person_selling'] ?? $costSheet['totals']['per_person_cost'], 2) }}</span>
    </div>
    <div class="totals-row grand">
        <span>Total Due</span>
        <span>${{ number_format($costSheet['totals']['selling_total'], 2) }}</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- TERMS                                          --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="terms">
    <h3>Payment Terms</h3>
    <ul>
        <li>50% non-refundable deposit required to confirm booking.</li>
        <li>Balance payable 30 days before travel date.</li>
        <li>This quotation is valid for 14 days from the date of issue.</li>
    </ul>

    <h3>Cancellation Policy</h3>
    <ul>
        <li>30+ days before departure — 25% cancellation fee</li>
        <li>15–29 days before departure — 50% cancellation fee</li>
        <li>0–14 days before departure — 100% cancellation fee</li>
    </ul>
</div>

@if($template->footer_text)
<div class="pdf-footer">{{ $template->footer_text }}</div>
@endif

</body>
</html>
