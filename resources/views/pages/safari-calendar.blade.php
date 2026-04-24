@extends('layouts.app')
@section('title', 'Safari Calendar - Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'safari-calendar'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Safari Calendar</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>

        <div class="content-area">
            <div class="card" style="margin-bottom:12px;">
                <form method="GET" action="{{ url('/operations/safari-calendar') }}" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;padding:14px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Status</label>
                        <select name="status" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;">
                            <option value="">All</option>
                            @foreach(['inquiry', 'provisional', 'confirmed', 'cancelled', 'sample'] as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">From</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">To</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;">
                    </div>
                    <a href="{{ url('/operations/safari-calendar') }}" class="btn" style="background:#e2e8f0;color:#0f172a;border:none;padding:10px 14px;border-radius:8px;text-decoration:none;">Reset</a>
                    <button type="submit" class="btn" style="background:#0f766e;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;">Filter</button>
                </form>
            </div>

            <div class="card" style="padding:12px;overflow:auto;">
                <table class="calendar-grid" style="border-collapse:collapse;min-width:980px;width:100%;font-size:11px;"></table>
            </div>
        </div>
    </div>
</div>

<script>
const CALENDAR_ITINERARIES = @json($calendarItineraries);

function statusColor(status) {
    switch ((status || '').toLowerCase()) {
        case 'confirmed': return '#bbf7d0';
        case 'provisional': return '#bfdbfe';
        case 'cancelled': return '#fecaca';
        default: return '#fde68a';
    }
}

function renderCalendar() {
    const table = document.querySelector('.calendar-grid');
    if (!CALENDAR_ITINERARIES.length) {
        table.outerHTML = '<div style="padding:12px;color:#64748b;">No bookings found for selected filters.</div>';
        return;
    }

    const start = new Date(Math.min(...CALENDAR_ITINERARIES.map(i => new Date(i.start_date).getTime())));
    const end = new Date(Math.max(...CALENDAR_ITINERARIES.map(i => new Date(i.end_date).getTime())));

    const dates = [];
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        dates.push(new Date(d));
    }

    let html = '<thead><tr><th style="position:sticky;left:0;background:#fff;z-index:2;border:1px solid #e2e8f0;padding:6px 8px;text-align:left;">Booking</th>';
    dates.forEach(d => {
        html += `<th style="border:1px solid #e2e8f0;padding:6px 4px;">${d.getDate()}/${d.getMonth()+1}</th>`;
    });
    html += '</tr></thead><tbody>';

    CALENDAR_ITINERARIES.forEach(itn => {
        const status = (itn.builder_state?.save?.status || 'inquiry').toLowerCase();
        const s = new Date(itn.start_date);
        const e = new Date(itn.end_date);

        html += `<tr><td style="position:sticky;left:0;background:#fff;z-index:1;border:1px solid #e2e8f0;padding:6px 8px;white-space:nowrap;"><a href="/itineraries/${itn.id}/builder" style="color:#0f172a;text-decoration:none;font-weight:700;">${itn.client_name}</a> <span style="font-size:10px;color:#64748b;">${status}</span></td>`;

        dates.forEach(d => {
            const active = d >= s && d <= e;
            html += `<td style="border:1px solid #e2e8f0;padding:6px 4px;background:${active ? statusColor(status) : '#fff'};"></td>`;
        });

        html += '</tr>';
    });

    html += '</tbody>';
    table.innerHTML = html;
}

renderCalendar();
</script>
@endsection
