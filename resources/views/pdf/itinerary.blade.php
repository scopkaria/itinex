<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $itinerary->client_name }} — Itinerary</title>
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: '{{ $template->font ?? "Helvetica" }}', Helvetica, Arial, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.5; }

    :root { --primary: {{ $template->primary_color ?? '#4f46e5' }}; }

    /* ── Cover Page ──────────────────────────────── */
    .cover {
        page-break-after: always;
        height: 100%;
        background: linear-gradient(160deg, {{ $template->primary_color ?? '#4f46e5' }} 0%, #1e1b4b 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 80px 60px;
        position: relative;
    }
    .cover-logo { margin-bottom: 40px; }
    .cover-logo img { max-height: 60px; }
    .cover-title { font-size: 36px; font-weight: 700; letter-spacing: -0.5px; margin-bottom: 8px; }
    .cover-subtitle { font-size: 16px; opacity: 0.85; margin-bottom: 32px; }
    .cover-meta { font-size: 13px; opacity: 0.75; line-height: 1.8; }
    .cover-meta strong { opacity: 1; }
    .cover-footer { position: absolute; bottom: 40px; left: 60px; right: 60px; font-size: 10px; opacity: 0.5; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 12px; }

    /* ── Content Pages ───────────────────────────── */
    .page { padding: 40px 50px; page-break-after: always; }
    .page:last-child { page-break-after: auto; }

    .page-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid {{ $template->primary_color ?? '#4f46e5' }}; padding-bottom: 12px; margin-bottom: 24px; }
    .page-header h2 { font-size: 18px; font-weight: 700; color: {{ $template->primary_color ?? '#4f46e5' }}; }
    .page-header .company-name { font-size: 11px; color: #6b7280; }

    /* ── Day Card ─────────────────────────────────── */
    .day-card { margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
    .day-header { background: {{ $template->primary_color ?? '#4f46e5' }}; color: #fff; padding: 10px 16px; font-size: 13px; font-weight: 600; }
    .day-header span { font-weight: 400; opacity: 0.8; margin-left: 8px; font-size: 11px; }
    .day-body { padding: 14px 16px; }
    .day-item { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 11px; }
    .day-item:last-child { border-bottom: none; }
    .day-item .item-type { display: inline-block; background: #eef2ff; color: {{ $template->primary_color ?? '#4f46e5' }}; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; min-width: 70px; text-align: center; }
    .day-item .item-name { flex: 1; margin-left: 12px; font-weight: 500; }
    .day-empty { color: #9ca3af; font-size: 11px; font-style: italic; }

    /* ── Summary Table ────────────────────────────── */
    table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px; }
    th { text-align: left; padding: 8px 12px; background: #f9fafb; color: #6b7280; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; }
    td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
    .text-right { text-align: right; }
    .font-bold { font-weight: 700; }

    /* ── Totals Box ───────────────────────────────── */
    .totals-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-top: 20px; }
    .totals-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
    .totals-row.grand { font-size: 16px; font-weight: 700; color: {{ $template->primary_color ?? '#4f46e5' }}; border-top: 2px solid {{ $template->primary_color ?? '#4f46e5' }}; margin-top: 8px; padding-top: 10px; }

    /* ── Policies Section ─────────────────────────── */
    .policies { font-size: 10px; color: #6b7280; line-height: 1.6; }
    .policies h3 { font-size: 12px; color: #1f2937; font-weight: 700; margin: 14px 0 6px; }
    .policies ul { padding-left: 16px; }
    .policies li { margin-bottom: 3px; }

    /* ── Footer ───────────────────────────────────── */
    .pdf-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 50px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; text-align: center; background: #fff; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════ --}}
{{-- COVER PAGE                                     --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="cover">
    @if($template->logo)
    <div class="cover-logo">
        <img src="{{ public_path('storage/' . $template->logo) }}" alt="Logo">
    </div>
    @endif
    <div class="cover-title">{{ $itinerary->title ?? $itinerary->client_name . "'s Safari" }}</div>
    <div class="cover-subtitle">A Tailored Travel Experience</div>
    <div class="cover-meta">
        <strong>Prepared for:</strong> {{ $itinerary->client_name }}<br>
        <strong>Dates:</strong> {{ $itinerary->start_date->format('M d, Y') }} — {{ $itinerary->end_date->format('M d, Y') }}<br>
        <strong>Duration:</strong> {{ $itinerary->total_days }} Days / {{ $itinerary->total_days - 1 }} Nights<br>
        <strong>Travellers:</strong> {{ $itinerary->number_of_people }} {{ $itinerary->number_of_people === 1 ? 'Person' : 'People' }}<br>
        <strong>Prepared by:</strong> {{ $company->name }}
    </div>
    <div class="cover-footer">
        {{ $company->name }} {{ $company->phone ? '· ' . $company->phone : '' }} {{ $company->email ? '· ' . $company->email : '' }}
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- DAY-BY-DAY ITINERARY                           --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="page">
    <div class="page-header">
        <h2>Day-by-Day Itinerary</h2>
        <div class="company-name">{{ $company->name }}</div>
    </div>

    @foreach($itinerary->days as $day)
    <div class="day-card">
        <div class="day-header">
            Day {{ $day->day_number }}
            @if($day->date)
            <span>{{ \Carbon\Carbon::parse($day->date)->format('l, M d, Y') }}</span>
            @endif
        </div>
        <div class="day-body">
            @if($day->items->count())
                @foreach($day->items as $item)
                @php
                    $ref = $item->reference();
                    $label = match($item->type) {
                        'hotel' => $ref ? ($ref->hotel?->name . ' — ' . $ref->roomType?->type . ', ' . $ref->mealPlan?->name) : 'Accommodation',
                        'transport' => $ref ? ($ref->name . ' (' . $ref->capacity . ' pax)') : 'Transport',
                        'park_fee' => $ref ? $ref->park_name : 'Park Fee',
                        'flight' => $ref ? ($ref->name . ' · ' . $ref->origin . ' → ' . $ref->destination) : 'Flight',
                        'activity' => $ref ? $ref->name : 'Activity',
                        'extra' => $ref ? $ref->name : 'Extra',
                        default => ucfirst($item->type),
                    };
                    $typeLabel = match($item->type) {
                        'hotel' => 'Hotel', 'transport' => 'Transport', 'park_fee' => 'Park',
                        'flight' => 'Flight', 'activity' => 'Activity', 'extra' => 'Extra', default => $item->type,
                    };
                @endphp
                <div class="day-item">
                    <span class="item-type">{{ $typeLabel }}</span>
                    <span class="item-name">{{ $label }}</span>
                </div>
                @endforeach
            @else
                <div class="day-empty">Leisure day — free time to explore.</div>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- PRICING SUMMARY                                --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="page">
    <div class="page-header">
        <h2>Pricing Summary</h2>
        <div class="company-name">{{ $company->name }}</div>
    </div>

    <div class="totals-box">
        <div class="totals-row"><span>Duration</span><span>{{ $itinerary->total_days }} Days / {{ $itinerary->total_days - 1 }} Nights</span></div>
        <div class="totals-row"><span>Number of Travellers</span><span>{{ $itinerary->number_of_people }}</span></div>
        <div class="totals-row"><span>Price Per Person</span><span>${{ number_format($costSheet['totals']['per_person_cost'], 2) }}</span></div>
        <div class="totals-row grand"><span>Total Package Price</span><span>${{ number_format($costSheet['totals']['selling_total'], 2) }}</span></div>
    </div>

    {{-- Includes --}}
    <div class="policies" style="margin-top:28px;">
        <h3>Package Includes</h3>
        <ul>
            @if(count($costSheet['breakdown']['accommodation']))<li>Accommodation as per itinerary ({{ count($costSheet['breakdown']['accommodation']) }} night{{ count($costSheet['breakdown']['accommodation']) > 1 ? 's' : '' }})</li>@endif
            @if(count($costSheet['breakdown']['park_fees']))<li>National park & conservation fees</li>@endif
            @if(count($costSheet['breakdown']['transport']))<li>Ground transportation with professional driver-guide</li>@endif
            @if(count($costSheet['breakdown']['flights']))<li>Domestic flights as specified</li>@endif
            @if(count($costSheet['breakdown']['extras']))<li>Activities and extras as listed</li>@endif
            <li>Meals as specified in accommodation plan</li>
            <li>Drinking water during game drives</li>
        </ul>

        <h3>Package Excludes</h3>
        <ul>
            <li>International flights</li>
            <li>Travel insurance</li>
            <li>Visa fees</li>
            <li>Tips and gratuities</li>
            <li>Personal expenses</li>
            <li>Items not mentioned in the itinerary</li>
        </ul>

        <h3>Booking Conditions</h3>
        <ul>
            <li>50% deposit required to confirm booking</li>
            <li>Balance due 30 days before departure</li>
            <li>Cancellation within 30 days: 50% charge</li>
            <li>Cancellation within 14 days: 100% charge</li>
            <li>Prices subject to change until confirmed</li>
        </ul>
    </div>
</div>

@if($template->footer_text)
<div class="pdf-footer">{{ $template->footer_text }}</div>
@endif

</body>
</html>
