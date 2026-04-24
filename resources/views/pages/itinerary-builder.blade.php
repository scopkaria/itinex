@extends('layouts.app')
@section('title', ($itinerary->title ?? $itinerary->client_name) . ' - Step Builder')
@section('body')
<style>
    .builder-shell { display:grid; grid-template-columns: 240px 1fr 320px; gap:16px; align-items:start; }
    .builder-nav, .builder-main, .builder-live { background:#fff; border:1px solid var(--border-light); border-radius:14px; }
    .builder-nav { padding:14px; position:sticky; top:80px; }
    .builder-main { padding:18px; }
    .builder-live { padding:16px; position:sticky; top:80px; }
    .step-link { display:flex; align-items:center; gap:10px; width:100%; text-align:left; border:1px solid #e2e8f0; background:#f8fafc; border-radius:10px; padding:9px 10px; margin-bottom:8px; font-size:13px; cursor:pointer; }
    .step-link.active { background:#0f766e; border-color:#0f766e; color:#fff; }
    .step-num { width:20px; height:20px; border-radius:999px; background:#fff; color:#0f172a; font-size:11px; display:grid; place-items:center; font-weight:700; }
    .step-pane { display:none; }
    .step-pane.active { display:block; }
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
    .field { margin-bottom:12px; }
    .field label { display:block; font-size:12px; font-weight:700; margin-bottom:5px; color:#475569; }
    .field input, .field select, .field textarea { width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-size:13px; }
    .section-title { font-size:16px; font-weight:800; margin:0 0 12px; color:#0f172a; }
    .subtle { font-size:12px; color:#64748b; margin-bottom:12px; }
    .btn-row { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
    .btn-a { border:none; border-radius:8px; padding:9px 14px; font-size:13px; font-weight:700; cursor:pointer; }
    .btn-primary { background:#0f766e; color:#fff; }
    .btn-secondary { background:#e2e8f0; color:#0f172a; }
    .table { width:100%; border-collapse:collapse; font-size:12px; }
    .table th, .table td { border:1px solid #e2e8f0; padding:6px 8px; }
    .table th { background:#f8fafc; text-align:left; }
    .msg { font-size:12px; padding:8px 10px; border-radius:8px; margin-top:8px; }
    .msg.ok { background:#ecfeff; color:#0f766e; border:1px solid #99f6e4; }
    .msg.err { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
    .cal { overflow:auto; border:1px solid #e2e8f0; border-radius:10px; }
    .cal-grid { border-collapse:collapse; min-width:940px; width:100%; font-size:11px; }
    .cal-grid th, .cal-grid td { border:1px solid #e2e8f0; padding:5px 6px; text-align:center; }
    .cal-grid th:first-child, .cal-grid td:first-child { text-align:left; position:sticky; left:0; background:#fff; min-width:180px; }
    .badge-stat { font-size:10px; font-weight:700; border-radius:999px; padding:2px 7px; display:inline-block; }
    .s-inquiry { background:#fef3c7; color:#92400e; }
    .s-provisional { background:#dbeafe; color:#1d4ed8; }
    .s-confirmed { background:#dcfce7; color:#166534; }
    .s-cancelled { background:#fee2e2; color:#991b1b; }
    @media (max-width: 1200px) { .builder-shell { grid-template-columns:1fr; } .builder-nav,.builder-live { position:static; } }
</style>

<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'itineraries'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:18px;font-weight:700;">Step Builder - {{ $itinerary->title ?? $itinerary->client_name }}</h2>
            <div class="topbar-user">
                <a href="{{ url('/operations/safari-calendar') }}" class="btn-secondary btn-a" style="text-decoration:none;">Open Safari Calendar</a>
                <a href="{{ url('/itineraries/' . $itinerary->id) }}" class="btn-secondary btn-a" style="text-decoration:none;">Open Classic View</a>
            </div>
        </header>

        <div class="content-area">
            <div class="builder-shell">
                <aside class="builder-nav">
                    <button class="step-link active" data-step="1"><span class="step-num">1</span>General Info</button>
                    <button class="step-link" data-step="2"><span class="step-num">2</span>Pricing Setup</button>
                    <button class="step-link" data-step="3"><span class="step-num">3</span>Guests & Rooms</button>
                    <button class="step-link" data-step="4"><span class="step-num">4</span>Services</button>
                    <button class="step-link" data-step="5"><span class="step-num">5</span>Review & Pricing</button>
                    <button class="step-link" data-step="6"><span class="step-num">6</span>Save / Confirm</button>
                </aside>

                <main class="builder-main">
                    <section class="step-pane active" data-step="1">
                        <h3 class="section-title">Step 1 - General Info</h3>
                        <div class="grid-2">
                            <div class="field"><label>Booking Name *</label><input id="booking_name" value="{{ $itinerary->client_name }}"></div>
                            <div class="field"><label>Booking Type</label><select id="booking_type"><option value="agent">Agent</option><option value="direct">Direct</option></select></div>
                        </div>
                        <div class="grid-2">
                            <div class="field"><label>Agent Name</label><input id="agent_name" placeholder="Required if Agent"></div>
                            <div class="field"><label>Safari Type</label><select id="safari_type"><option value="accommodation">Accommodation</option><option value="non_accommodation">Non-accommodation</option></select></div>
                        </div>
                        <h4 class="section-title" style="font-size:14px;">Arrival</h4>
                        <div class="grid-3">
                            <div class="field"><label>Date</label><input type="date" id="arrival_date"></div>
                            <div class="field"><label>Time</label><input type="time" id="arrival_time"></div>
                            <div class="field"><label>Means</label><select id="arrival_means"><option>flight</option><option>road</option><option>train</option><option>boat</option></select></div>
                        </div>
                        <div class="field"><label>Point</label><select id="arrival_point"></select></div>
                        <h4 class="section-title" style="font-size:14px;">Departure</h4>
                        <div class="grid-3">
                            <div class="field"><label>Date</label><input type="date" id="departure_date"></div>
                            <div class="field"><label>Time</label><input type="time" id="departure_time"></div>
                            <div class="field"><label>Means</label><select id="departure_means"><option>flight</option><option>road</option><option>train</option><option>boat</option></select></div>
                        </div>
                        <div class="field"><label>Point</label><select id="departure_point"></select></div>
                        <div class="btn-row"><button class="btn-a btn-primary" onclick="goStep(2)">Next</button></div>
                    </section>

                    <section class="step-pane" data-step="2">
                        <h3 class="section-title">Step 2 - Pricing Setup</h3>
                        <div class="grid-3">
                            <div class="field"><label>Markup %</label><input type="number" step="0.01" min="0" id="markup_pct" value="{{ (float) $itinerary->markup_percentage }}"></div>
                            <div class="field"><label>Discount Name</label><input id="discount_name" placeholder="Promo / Contract"></div>
                            <div class="field"><label>Discount Value</label><input type="number" step="0.01" min="0" id="discount_value"></div>
                        </div>
                        <div class="field"><label>Markup Covers</label>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:12px;">
                                <label><input type="checkbox" class="mk-cover" value="accommodation" checked> Accommodation</label>
                                <label><input type="checkbox" class="mk-cover" value="park_fee" checked> Park Fees</label>
                                <label><input type="checkbox" class="mk-cover" value="flights" checked> Flights</label>
                                <label><input type="checkbox" class="mk-cover" value="transfers" checked> Transfers</label>
                                <label><input type="checkbox" class="mk-cover" value="transport" checked> Transport (Day/KM)</label>
                                <label><input type="checkbox" class="mk-cover" value="extras" checked> Extras</label>
                                <label><input type="checkbox" class="mk-cover" value="packages" checked> Packages</label>
                            </div>
                        </div>
                        <div class="btn-row"><button class="btn-a btn-secondary" onclick="goStep(1)">Back</button><button class="btn-a btn-primary" onclick="goStep(3)">Next</button></div>
                    </section>

                    <section class="step-pane" data-step="3">
                        <h3 class="section-title">Step 3 - Guests & Rooms</h3>
                        <p class="subtle">No empty rooms and at least one adult are required.</p>
                        <div id="rooms-wrap">
                            <table class="table" id="rooms-table">
                                <thead><tr><th>Room</th><th>Type</th><th>Adults</th><th>Teens</th><th>Children</th><th>Ages</th><th></th></tr></thead>
                                <tbody></tbody>
                            </table>
                            <div class="btn-row"><button class="btn-a btn-secondary" type="button" onclick="addRoomRow()">+ Add Room</button></div>
                        </div>
                        <div class="msg err" id="rooms-err" style="display:none;"></div>
                        <div class="btn-row"><button class="btn-a btn-secondary" onclick="goStep(2)">Back</button><button class="btn-a btn-primary" onclick="validateRoomsAndNext()">Next</button></div>
                    </section>

                    <section class="step-pane" data-step="4">
                        <h3 class="section-title">Step 4 - Services</h3>
                        <div class="field"><label>Add Service</label><select id="service_type" onchange="renderServiceFields()"><option value="accommodation">Accommodation</option><option value="flight">Flight</option><option value="transfer">Transfer</option><option value="transport">Transport (Per Day)</option><option value="park_fee">Park Fee</option><option value="package">Package</option><option value="extra">Extras</option></select></div>
                        <div id="service-fields"></div>
                        <div class="field"><label>Add to Day</label><select id="service_day">@foreach($itinerary->days as $d)<option value="{{ $d->id }}">Day {{ $d->day_number }} - {{ $d->date?->format('Y-m-d') }}</option>@endforeach</select></div>
                        <div class="btn-row"><button class="btn-a btn-primary" type="button" onclick="quoteService()">Quote Service</button><button class="btn-a btn-secondary" type="button" onclick="addQuotedService()">Add Quoted Service</button></div>
                        <div id="service-msg" class="msg" style="display:none;"></div>
                        <div class="btn-row"><button class="btn-a btn-secondary" onclick="goStep(3)">Back</button><button class="btn-a btn-primary" onclick="goStep(5)">Next</button></div>
                    </section>

                    <section class="step-pane" data-step="5">
                        <h3 class="section-title">Step 5 - Review & Pricing Summary</h3>
                        <div class="table">
                            <table class="table">
                                <tbody>
                                    <tr><th>Accommodation total</th><td id="live-acc">${{ number_format($costSheet['totals']['accommodation_total'], 2) }}</td></tr>
                                    <tr><th>Flights total</th><td id="live-flight">${{ number_format($costSheet['totals']['flight_total'], 2) }}</td></tr>
                                    <tr><th>Transport total</th><td id="live-transport">${{ number_format($costSheet['totals']['transport_total'], 2) }}</td></tr>
                                    <tr><th>Extras total</th><td id="live-extra">${{ number_format($costSheet['totals']['extras_total'], 2) }}</td></tr>
                                    <tr><th>Subtotal</th><td id="live-subtotal"></td></tr>
                                    <tr><th>Markup</th><td id="live-markup"></td></tr>
                                    <tr><th>Discount</th><td id="live-discount"></td></tr>
                                    <tr><th>VAT</th><td id="live-vat"></td></tr>
                                    <tr><th>Grand Total</th><td id="live-grand" style="font-weight:800;"></td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="btn-row"><button class="btn-a btn-secondary" onclick="goStep(4)">Back</button><button class="btn-a btn-primary" onclick="goStep(6)">Next</button></div>
                    </section>

                    <section class="step-pane" data-step="6">
                        <h3 class="section-title">Step 6 - Save / Confirm</h3>
                        <div class="grid-2">
                            <div class="field"><label>Status</label><select id="status"><option>inquiry</option><option>provisional</option><option>confirmed</option><option>cancelled</option><option>sample</option></select></div>
                            <div class="field"><label>Assigned To</label><input id="assigned_to" value="{{ auth()->user()->name }}"></div>
                        </div>
                        <div class="grid-2">
                            <div class="field"><label>Branch</label><input id="branch" placeholder="Main / Arusha / Zanzibar"></div>
                            <div class="field"><label>Currency</label><input id="currency" value="USD"></div>
                        </div>
                        <div class="btn-row"><button class="btn-a btn-secondary" onclick="goStep(5)">Back</button><button class="btn-a btn-primary" onclick="saveBuilderState()">Save Builder State</button></div>
                    </section>

                </main>

                <aside class="builder-live">
                    <h3 class="section-title" style="font-size:14px;">Live Engine Panel</h3>
                    <div style="font-size:12px;color:#475569;">Current itinerary totals are shown and updated with your latest quoted service preview.</div>
                    <div id="quote-preview" class="msg ok" style="display:none;margin-top:10px;"></div>
                    <div class="msg" style="margin-top:10px;background:#f8fafc;border:1px solid #e2e8f0;">Final total is always recalculated server-side after items are added.</div>
                </aside>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
const ITINERARY_ID = {{ $itinerary->id }};
const DESTINATIONS = @json($destinations);
const HOTELS = @json($hotels);
const HOTEL_RATES = @json($hotelRates);
const FLIGHT_PROVIDERS = @json($flightProviders);
const TRANSPORT_PROVIDERS = @json($transportProviders);
const ACTIVITIES = @json($activities);
const EXTRAS = @json($extras);
const PACKAGES = @json($packages);
let builderState = @json($builderState ?? []);
let lastQuote = null;

function goStep(step) {
    document.querySelectorAll('.step-link').forEach(el => el.classList.toggle('active', Number(el.dataset.step) === step));
    document.querySelectorAll('.step-pane').forEach(el => el.classList.toggle('active', Number(el.dataset.step) === step));
    if (step === 5) updateLivePanel();
}

document.querySelectorAll('.step-link').forEach(btn => btn.addEventListener('click', () => goStep(Number(btn.dataset.step))));

function optionHtml(rows, valueKey = 'id', textKey = 'name', placeholder = 'Select') {
    let html = `<option value="">${placeholder}</option>`;
    rows.forEach(r => { html += `<option value="${r[valueKey]}">${r[textKey]}</option>`; });
    return html;
}

function initGeneralDropdowns() {
    document.getElementById('arrival_point').innerHTML = optionHtml(DESTINATIONS);
    document.getElementById('departure_point').innerHTML = optionHtml(DESTINATIONS);
}

function addRoomRow(data = {}) {
    const tbody = document.querySelector('#rooms-table tbody');
    const idx = tbody.children.length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${idx}</td><td><select class="room-type"><option>Double</option><option>Twin</option><option>Single</option><option>Triple</option><option>Family</option></select></td><td><input type="number" class="room-adults" min="0" value="${data.adults || 2}"></td><td><input type="number" class="room-teens" min="0" value="${data.teens || 0}"></td><td><input type="number" class="room-children" min="0" value="${data.children || 0}"></td><td><input type="text" class="room-ages" placeholder="e.g. 4,7"></td><td><button class="btn-a btn-secondary" type="button" onclick="this.closest('tr').remove();">Del</button></td>`;
    tbody.appendChild(tr);
}

function validateRoomsAndNext() {
    const safariType = document.getElementById('safari_type').value;
    if (safariType === 'non_accommodation') {
        goStep(4);
        return;
    }

    const rows = [...document.querySelectorAll('#rooms-table tbody tr')];
    const err = document.getElementById('rooms-err');
    if (rows.length === 0) {
        err.style.display = 'block';
        err.textContent = 'Add at least one room.';
        return;
    }

    let adults = 0;
    rows.forEach(r => adults += Number(r.querySelector('.room-adults').value || 0));
    if (adults < 1) {
        err.style.display = 'block';
        err.textContent = 'At least 1 adult is required per booking.';
        return;
    }

    err.style.display = 'none';
    goStep(4);
}

function roomsTotals() {
    const rows = [...document.querySelectorAll('#rooms-table tbody tr')];
    return rows.reduce((acc, row) => {
        acc.adults += Number(row.querySelector('.room-adults').value || 0);
        acc.teens += Number(row.querySelector('.room-teens').value || 0);
        acc.children += Number(row.querySelector('.room-children').value || 0);
        const agesRaw = (row.querySelector('.room-ages').value || '').split(',').map(v => Number(v.trim())).filter(v => !Number.isNaN(v));
        acc.child_ages.push(...agesRaw);
        return acc;
    }, {adults: 0, teens: 0, children: 0, child_ages: []});
}

function renderServiceFields() {
    const t = document.getElementById('service_type').value;
    const box = document.getElementById('service-fields');

    if (t === 'accommodation') {
        box.innerHTML = `<div class="grid-3"><div class="field"><label>Destination</label><select id="svc_destination">${optionHtml(DESTINATIONS)}</select></div><div class="field"><label>Property</label><select id="svc_hotel"></select></div><div class="field"><label>Meal Plan</label><select id="svc_meal"><option value="">Any</option></select></div></div><div class="grid-3"><div class="field"><label>Arrival Date</label><input type="date" id="svc_arrival"></div><div class="field"><label>Nights</label><input type="number" id="svc_nights" min="1" value="1"></div><div class="field"><label>Rate Type</label><select id="svc_rate_type"><option>STO</option><option>Special</option><option>Manual</option></select></div></div>`;
        document.getElementById('svc_destination').addEventListener('change', () => {
            const id = Number(document.getElementById('svc_destination').value || 0);
            const hotels = HOTELS.filter(h => Number(h.location_id) === id);
            document.getElementById('svc_hotel').innerHTML = optionHtml(hotels);
        });
        document.getElementById('svc_hotel').addEventListener('change', () => {
            const hotelId = Number(document.getElementById('svc_hotel').value || 0);
            const uniqueMeals = {};
            HOTEL_RATES.filter(r => Number(r.hotel_id) === hotelId).forEach(r => {
                if (r.meal_plan) uniqueMeals[r.meal_plan_id] = r.meal_plan.name;
            });
            const rows = Object.entries(uniqueMeals).map(([id, name]) => ({id, name}));
            document.getElementById('svc_meal').innerHTML = optionHtml(rows, 'id', 'name', 'Any');
        });
        return;
    }

    if (t === 'flight') {
        box.innerHTML = `<div class="grid-3"><div class="field"><label>Provider</label><select id="svc_provider"></select></div><div class="field"><label>Route</label><select id="svc_route"></select></div><div class="field"><label>Date</label><input type="date" id="svc_date"></div></div><div class="grid-3"><div class="field"><label>Rate Type</label><select id="svc_rate_type"></select></div><div class="field"><label>PAX (auto from rooms)</label><input id="svc_pax_view" disabled></div><div class="field"><label>Child Ages</label><input id="svc_child_ages" placeholder="e.g. 4,8"></div></div>`;
        document.getElementById('svc_provider').innerHTML = optionHtml(FLIGHT_PROVIDERS);
        document.getElementById('svc_provider').addEventListener('change', () => {
            const provider = FLIGHT_PROVIDERS.find(p => Number(p.id) === Number(document.getElementById('svc_provider').value || 0));
            const routes = (provider?.routes || []).map(r => ({id: r.id, name: `${r.origin_destination?.name || '-'} -> ${r.arrival_destination?.name || '-'}`}));
            const types = (provider?.rate_types || []).map(r => ({id: r.name, name: r.name}));
            document.getElementById('svc_route').innerHTML = optionHtml(routes);
            document.getElementById('svc_rate_type').innerHTML = optionHtml(types, 'id', 'name', 'STO');
        });
        const pax = roomsTotals();
        document.getElementById('svc_pax_view').value = `${pax.adults}A / ${pax.teens}T / ${pax.children}C`;
        return;
    }

    if (t === 'transfer') {
        box.innerHTML = `<div class="grid-3"><div class="field"><label>Company</label><select id="svc_provider"></select></div><div class="field"><label>Route</label><select id="svc_route"></select></div><div class="field"><label>Vehicle Type</label><select id="svc_vehicle_type"></select></div></div><div class="field"><label>Date</label><input type="date" id="svc_date"></div>`;
        document.getElementById('svc_provider').innerHTML = optionHtml(TRANSPORT_PROVIDERS);
        document.getElementById('svc_provider').addEventListener('change', () => {
            const provider = TRANSPORT_PROVIDERS.find(p => Number(p.id) === Number(document.getElementById('svc_provider').value || 0));
            const routes = (provider?.transfer_routes || []).map(r => ({id: r.id, name: `${r.origin_destination?.name || '-'} -> ${r.arrival_destination?.name || '-'}`}));
            const types = (provider?.vehicle_types || []).map(v => ({id: v.id, name: v.name}));
            document.getElementById('svc_route').innerHTML = optionHtml(routes);
            document.getElementById('svc_vehicle_type').innerHTML = optionHtml(types);
        });
        return;
    }

    if (t === 'transport') {
        box.innerHTML = `<div class="grid-3"><div class="field"><label>Company</label><select id="svc_provider"></select></div><div class="field"><label>Vehicle Type</label><select id="svc_vehicle_type"></select></div><div class="field"><label>Days</label><input type="number" id="svc_days" min="1" value="1"></div></div><div class="grid-3"><div class="field"><label>Date</label><input type="date" id="svc_date"></div><div class="field"><label>Empty Run</label><input id="svc_empty_run" placeholder="optional"></div><div class="field"><label>Dead Leg</label><input id="svc_dead_leg" placeholder="optional"></div></div>`;
        document.getElementById('svc_provider').innerHTML = optionHtml(TRANSPORT_PROVIDERS);
        document.getElementById('svc_provider').addEventListener('change', () => {
            const provider = TRANSPORT_PROVIDERS.find(p => Number(p.id) === Number(document.getElementById('svc_provider').value || 0));
            const types = (provider?.vehicle_types || []).map(v => ({id: v.id, name: v.name}));
            document.getElementById('svc_vehicle_type').innerHTML = optionHtml(types);
        });
        return;
    }

    if (t === 'park_fee') {
        box.innerHTML = `<div class="grid-3"><div class="field"><label>Destination</label><select id="svc_destination">${optionHtml(DESTINATIONS)}</select></div><div class="field"><label>Date</label><input type="date" id="svc_date"></div><div class="field"><label>PAX Type</label><select id="svc_pax_type"><option value="non_resident">Non Resident</option><option value="resident">Resident</option><option value="citizen">Citizen</option></select></div></div><div class="field"><label>Days</label><input type="number" id="svc_days" min="1" value="1"></div>`;
        return;
    }

    if (t === 'package') {
        box.innerHTML = `<div class="grid-3"><div class="field"><label>Package</label><select id="svc_package"></select></div><div class="field"><label>Nights</label><input type="number" id="svc_nights" min="1" value="1" disabled></div><div class="field"><label>Estimated Total</label><input type="number" step="0.01" min="0" id="svc_manual_total" disabled></div></div>`;
        document.getElementById('svc_package').innerHTML = optionHtml(PACKAGES);
        document.getElementById('svc_package').addEventListener('change', () => {
            const p = PACKAGES.find(x => Number(x.id) === Number(document.getElementById('svc_package').value || 0));
            if (!p) return;
            document.getElementById('svc_nights').value = Number(p.nights || 1);
            const pax = roomsTotals();
            const paxTotal = pax.adults + pax.teens + pax.children;
            const base = p.price_mode === 'per_group' ? Number(p.base_price) : Number(p.base_price) * Math.max(1, paxTotal);
            const withMarkup = base + (base * (Number(p.markup_percentage || 0) / 100));
            const discount = p.discount_mode === 'percent'
                ? withMarkup * (Number(p.discount_value || 0) / 100)
                : (p.discount_mode === 'fixed' ? Number(p.discount_value || 0) : 0);
            document.getElementById('svc_manual_total').value = Math.max(0, withMarkup - discount).toFixed(2);
        });
        return;
    }

    box.innerHTML = `<div class="grid-3"><div class="field"><label>Extra</label><select id="svc_extra">${optionHtml(EXTRAS)}</select></div><div class="field"><label>Activity</label><select id="svc_activity">${optionHtml(ACTIVITIES)}</select></div><div class="field"><label>Qty</label><input type="number" id="svc_qty" min="1" value="1"></div></div>`;
}

function buildPayload() {
    const t = document.getElementById('service_type').value;
    const pax = roomsTotals();

    if (t === 'accommodation') {
        return { destination_id: Number(document.getElementById('svc_destination').value || 0), hotel_id: Number(document.getElementById('svc_hotel').value || 0), meal_plan_id: Number(document.getElementById('svc_meal').value || 0), arrival_date: document.getElementById('svc_arrival').value, nights: Number(document.getElementById('svc_nights').value || 1), rate_type: document.getElementById('svc_rate_type').value, pax_total: pax.adults + pax.teens + pax.children };
    }
    if (t === 'flight') {
        const ageField = document.getElementById('svc_child_ages').value || '';
        return { provider_id: Number(document.getElementById('svc_provider').value || 0), route_id: Number(document.getElementById('svc_route').value || 0), date: document.getElementById('svc_date').value, rate_type: document.getElementById('svc_rate_type').value || 'STO', adults: pax.adults, teens: pax.teens, children: pax.children, child_ages: ageField.split(',').map(v => Number(v.trim())).filter(v => !Number.isNaN(v)) };
    }
    if (t === 'transfer') {
        return { provider_id: Number(document.getElementById('svc_provider').value || 0), route_id: Number(document.getElementById('svc_route').value || 0), vehicle_type_id: Number(document.getElementById('svc_vehicle_type').value || 0), date: document.getElementById('svc_date').value };
    }
    if (t === 'transport') {
        return { provider_id: Number(document.getElementById('svc_provider').value || 0), vehicle_type_id: Number(document.getElementById('svc_vehicle_type').value || 0), date: document.getElementById('svc_date').value, days: Number(document.getElementById('svc_days').value || 1) };
    }
    if (t === 'park_fee') {
        return { destination_id: Number(document.getElementById('svc_destination').value || 0), date: document.getElementById('svc_date').value, pax_type: document.getElementById('svc_pax_type').value, adults: pax.adults, children: pax.children, days: Number(document.getElementById('svc_days').value || 1) };
    }
    if (t === 'package') {
        return { package_id: Number(document.getElementById('svc_package').value || 0), nights: Number(document.getElementById('svc_nights').value || 1), pax_total: pax.adults + pax.teens + pax.children };
    }
    return { extra_id: Number(document.getElementById('svc_extra').value || 0), activity_id: Number(document.getElementById('svc_activity').value || 0), quantity: Number(document.getElementById('svc_qty').value || 1), pax_total: pax.adults + pax.teens + pax.children };
}

function showServiceMsg(text, ok) {
    const el = document.getElementById('service-msg');
    el.style.display = 'block';
    el.className = 'msg ' + (ok ? 'ok' : 'err');
    el.textContent = text;
}

async function quoteService() {
    const service_type = document.getElementById('service_type').value;
    const payload = buildPayload();
    try {
        const res = await fetch(`/itineraries/${ITINERARY_ID}/builder/quote-service`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
            body: JSON.stringify({service_type, payload}),
        });
        const data = await res.json();
        if (!res.ok) {
            showServiceMsg(data.message || 'Quote failed', false);
            return;
        }
        lastQuote = data;
        showServiceMsg(`${data.label}: $${Number(data.base_total).toFixed(2)}`, true);
        document.getElementById('quote-preview').style.display = 'block';
        document.getElementById('quote-preview').textContent = `${data.label} - Base: $${Number(data.base_total).toFixed(2)}`;
        updateLivePanel();
    } catch (e) {
        showServiceMsg('Quote request failed.', false);
    }
}

async function addQuotedService() {
    if (!lastQuote || !lastQuote.item) {
        showServiceMsg('Quote a service first.', false);
        return;
    }

    const body = {
        itinerary_day_id: Number(document.getElementById('service_day').value),
        type: lastQuote.item.type,
        reference_id: Number(lastQuote.item.reference_id || 0),
        reference_source: lastQuote.item.reference_source || null,
        quantity: Number(lastQuote.item.quantity || 1),
        meta: lastQuote.item.meta || {},
        _token: CSRF,
    };

    const res = await fetch(`/itineraries/${ITINERARY_ID}/items`, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify(body),
    });

    if (res.ok) {
        showServiceMsg('Service item added. Refreshing page totals...', true);
        setTimeout(() => location.reload(), 600);
        return;
    }

    let errorText = 'Could not add service item.';
    try {
        const err = await res.json();
        if (err.message) errorText = err.message;
    } catch (_) {}
    showServiceMsg(errorText, false);
}

function updateLivePanel() {
    const subtotal = {{ (float) $costSheet['totals']['grand_total'] }} + (lastQuote ? Number(lastQuote.base_total || 0) : 0);
    const markupPct = Number(document.getElementById('markup_pct')?.value || 0);
    const discount = Number(document.getElementById('discount_value')?.value || 0);
    const vatPct = Number(builderState?.pricing?.vat_pct || 0);
    const markup = subtotal * (markupPct / 100);
    const netAfterDiscount = Math.max(0, subtotal + markup - discount);
    const vat = netAfterDiscount * (vatPct / 100);
    const grand = netAfterDiscount + vat;

    document.getElementById('live-subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('live-markup').textContent = `$${markup.toFixed(2)} (${markupPct.toFixed(2)}%)`;
    document.getElementById('live-discount').textContent = `-$${discount.toFixed(2)}`;
    document.getElementById('live-vat').textContent = `$${vat.toFixed(2)} (${vatPct.toFixed(2)}%)`;
    document.getElementById('live-grand').textContent = `$${grand.toFixed(2)}`;
}

function captureRoomsState() {
    return [...document.querySelectorAll('#rooms-table tbody tr')].map(r => ({
        type: r.querySelector('.room-type').value,
        adults: Number(r.querySelector('.room-adults').value || 0),
        teens: Number(r.querySelector('.room-teens').value || 0),
        children: Number(r.querySelector('.room-children').value || 0),
        ages: r.querySelector('.room-ages').value,
    }));
}

async function saveBuilderState() {
    builderState = {
        general: {
            booking_name: document.getElementById('booking_name').value,
            booking_type: document.getElementById('booking_type').value,
            agent_name: document.getElementById('agent_name').value,
            safari_type: document.getElementById('safari_type').value,
            arrival_date: document.getElementById('arrival_date').value,
            arrival_time: document.getElementById('arrival_time').value,
            arrival_means: document.getElementById('arrival_means').value,
            arrival_point: document.getElementById('arrival_point').value,
            departure_date: document.getElementById('departure_date').value,
            departure_time: document.getElementById('departure_time').value,
            departure_means: document.getElementById('departure_means').value,
            departure_point: document.getElementById('departure_point').value,
        },
        pricing: {
            markup_pct: Number(document.getElementById('markup_pct').value || 0),
            covers: [...document.querySelectorAll('.mk-cover:checked')].map(c => c.value),
            discount_name: document.getElementById('discount_name').value,
            discount_value: Number(document.getElementById('discount_value').value || 0),
            vat_pct: Number(builderState?.pricing?.vat_pct || 0),
        },
        rooms: captureRoomsState(),
        save: {
            status: document.getElementById('status').value,
            assigned_to: document.getElementById('assigned_to').value,
            branch: document.getElementById('branch').value,
            currency: document.getElementById('currency').value,
        }
    };

    const res = await fetch(`/itineraries/${ITINERARY_ID}/builder/state`, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify({builder_state: builderState}),
    });

    if (res.ok) {
        alert('Builder state saved.');
    } else {
        alert('Could not save builder state.');
    }
}

function applyState() {
    if (!builderState || Object.keys(builderState).length === 0) {
        addRoomRow();
        return;
    }

    const g = builderState.general || {};
    const p = builderState.pricing || {};
    const s = builderState.save || {};

    for (const [id, val] of Object.entries({
        booking_name: g.booking_name,
        booking_type: g.booking_type,
        agent_name: g.agent_name,
        safari_type: g.safari_type,
        arrival_date: g.arrival_date,
        arrival_time: g.arrival_time,
        arrival_means: g.arrival_means,
        arrival_point: g.arrival_point,
        departure_date: g.departure_date,
        departure_time: g.departure_time,
        departure_means: g.departure_means,
        departure_point: g.departure_point,
        markup_pct: p.markup_pct,
        discount_name: p.discount_name,
        discount_value: p.discount_value,
        status: s.status,
        assigned_to: s.assigned_to,
        branch: s.branch,
        currency: s.currency,
    })) {
        const el = document.getElementById(id);
        if (el && val !== undefined && val !== null) el.value = val;
    }

    const selectedCovers = new Set(p.covers || []);
    document.querySelectorAll('.mk-cover').forEach(c => c.checked = selectedCovers.has(c.value));

    const rows = builderState.rooms || [];
    if (rows.length === 0) {
        addRoomRow();
    } else {
        rows.forEach(addRoomRow);
    }

    toggleRoomsVisibility();
}

function toggleRoomsVisibility() {
    const hide = document.getElementById('safari_type').value === 'non_accommodation';
    document.getElementById('rooms-wrap').style.display = hide ? 'none' : 'block';
}

initGeneralDropdowns();
renderServiceFields();
applyState();
document.getElementById('safari_type').addEventListener('change', toggleRoomsVisibility);
</script>
@endsection
