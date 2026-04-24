@extends('layouts.app')
@section('title', 'Flight Module — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'flights'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Flight Module</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <!-- FLIGHT DASHBOARD HEADER -->
            <div class="page-header">
                <h1>Flight Providers Management</h1>
                <div class="action-group">
                    <button class="btn btn-secondary" onclick="showAddFlightModal()">+ Add Flight Provider</button>
                    <button class="btn btn-secondary" onclick="location.href='#destinations'">📍 Manage Destinations</button>
                </div>
            </div>

            <!-- MAIN DASHBOARD TABLE -->
            <div class="card">
                <div class="card-header">
                    <h3>Flight Providers Overview</h3>
                    <p style="color:#666;font-size:14px;margin:8px 0 0 0;">Manage airlines, routes, and pricing</p>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:5%;">#</th>
                                <th style="width:25%;">Flight Provider</th>
                                <th style="width:20%;">Route Summary</th>
                                <th style="width:15%;">Active Seasons</th>
                                <th style="width:15%;">Last Updated</th>
                                <th style="width:20%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="flight-providers-list">
                            <tr><td colspan="6"><div class="loading">Loading flight providers...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- QUICK STATS SECTION -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 24px;">
                <div class="stat-card">
                    <div class="stat-value" id="total-providers">0</div>
                    <div class="stat-label">Flight Providers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="total-aircraft">0</div>
                    <div class="stat-label">Aircraft Types</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="total-routes">0</div>
                    <div class="stat-label">Routes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="total-rates">0</div>
                    <div class="stat-label">Active Rates</div>
                </div>
            </div>
        </div>
    </div>

    <!-- SIDEBAR NAVIGATION FOR FLIGHT PRICING ENGINE -->
    <aside class="flight-pricing-sidebar" id="flight-pricing-sidebar">
        <div class="sidebar-header">
            <div>
                <h3>Flight Pricing</h3>
                <p id="flight-selected-provider" style="margin:6px 0 0 0;color:#666;font-size:12px;">No provider selected</p>
            </div>
            <button class="close-btn" onclick="closeFlightSidebar()">&times;</button>
        </div>

        <div class="sidebar-nav">
            <!-- YEARS -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">📅</span> Years
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightYearsView()">📋 Manage Years</button>
                    <button class="nav-item" onclick="openAddYearForm()">➕ Add Year</button>
                </div>
            </div>

            <!-- SEASONS -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">🌤️</span> Seasons
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightSeasonsView()">📋 All Seasons</button>
                    <button class="nav-item" onclick="openAddSeasonForm()">➕ Add Season</button>
                </div>
            </div>

            <!-- RATE TYPES -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">💰</span> Rate Types
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightRateTypesView()">📋 STO / Special / Contract</button>
                    <button class="nav-item" onclick="openAddRateTypeForm()">➕ Add Rate Type</button>
                </div>
            </div>

            <!-- RATE SCHEDULE -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">📊</span> Rate Schedule
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightScheduleView()">📋 Route Rates</button>
                    <button class="nav-item" onclick="openAddScheduleForm()">➕ Add Rate</button>
                </div>
            </div>

            <!-- CHARTER RATES -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">✈️</span> Charter Rates
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightCharterView()">📋 Charter Pricing</button>
                    <button class="nav-item" onclick="openAddCharterForm()">➕ Add Charter Rate</button>
                </div>
            </div>

            <!-- CHILD POLICY -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">👶</span> Child Policy
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightChildPolicyView()">📋 Age Pricing Rules</button>
                    <button class="nav-item" onclick="openAddChildPolicyForm()">➕ Add Rule</button>
                </div>
            </div>

            <!-- PAYMENT POLICY -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">💳</span> Payment Policy
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightPaymentPolicyView()">📋 Payment Terms</button>
                    <button class="nav-item" onclick="openAddPaymentPolicyForm()">➕ Add Policy</button>
                </div>
            </div>

            <!-- CANCELLATION POLICY -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">❌</span> Cancellation Policy
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFlightCancellationPolicyView()">📋 Cancellation Terms</button>
                    <button class="nav-item" onclick="openAddCancellationPolicyForm()">➕ Add Policy</button>
                </div>
            </div>
        </div>

        <div class="sidebar-footer">
            <button class="btn btn-secondary" style="width:100%;" onclick="closeFlightSidebar()">Close</button>
        </div>
    </aside>

    <div class="modal-backdrop" id="flight-modal" style="display:none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="flight-modal-title">Modal</h3>
                <button class="close-btn" onclick="closeFlightModal()">&times;</button>
            </div>
            <div class="modal-body" id="flight-modal-body"></div>
        </div>
    </div>
</div>

<style>
    .app-wrapper {
        display: flex;
    }
    .main-content {
        flex: 1;
    }
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding: 0 24px;
        padding-top: 24px;
    }
    .page-header h1 {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
    }
    .action-group {
        display: flex;
        gap: 12px;
    }
    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }
    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: 1px solid #ddd;
    }
    .btn-secondary:hover {
        background: #e8e8e8;
    }
    .card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin: 0 24px 24px;
    }
    .card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
    }
    .card-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    .table-wrap {
        overflow-x: auto;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table thead tr {
        background: #f5f5f5;
        border-bottom: 1px solid #e0e0e0;
    }
    .data-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }
    .data-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #555;
    }
    .data-table tbody tr:hover {
        background: #fafafa;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 6px;
        border-left: 4px solid #2563eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin: 0 24px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }
    .stat-label {
        font-size: 13px;
        color: #666;
        margin-top: 8px;
    }
    .flight-pricing-sidebar {
        position: fixed;
        right: 0;
        top: 0;
        width: 300px;
        height: 100vh;
        background: white;
        border-left: 1px solid #e0e0e0;
        box-shadow: -2px 0 8px rgba(0,0,0,0.1);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    .flight-pricing-sidebar.open {
        transform: translateX(0);
    }
    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
    }
    .sidebar-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
    }
    .sidebar-nav {
        flex: 1;
        padding: 12px 0;
    }
    .nav-section {
        border-bottom: 1px solid #f5f5f5;
    }
    .nav-title {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        margin: 0;
        cursor: pointer;
        user-select: none;
        font-weight: 500;
        font-size: 14px;
        color: #333;
    }
    .nav-title:hover {
        background: #f9f9f9;
    }
    .nav-title .toggle-icon {
        margin-left: auto;
        font-size: 12px;
    }
    .nav-items {
        padding: 8px 0;
    }
    .nav-item {
        display: block;
        width: 100%;
        padding: 10px 32px;
        background: none;
        border: none;
        text-align: left;
        font-size: 13px;
        color: #666;
        cursor: pointer;
        transition: color 0.2s;
    }
    .nav-item:hover {
        color: #2563eb;
        background: #f5f7ff;
    }
    .sidebar-footer {
        padding: 16px;
        border-top: 1px solid #f0f0f0;
    }
    .loading {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    .btn-primary {
        background: #2563eb;
        color: #fff;
    }
    .btn-primary:hover {
        background: #1e4fd8;
    }
    .action-btn {
        border: 1px solid #ddd;
        background: #fff;
        color: #333;
        border-radius: 4px;
        padding: 6px 10px;
        margin-right: 6px;
        cursor: pointer;
        font-size: 12px;
    }
    .action-btn:hover {
        background: #f7f7f7;
    }
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.55);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1100;
        padding: 20px;
    }
    .modal-card {
        width: min(860px, 100%);
        max-height: 90vh;
        overflow: auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f0f0f0;
        padding: 14px 18px;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 18px;
    }
    .modal-body {
        padding: 18px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-field label {
        font-size: 12px;
        color: #4b5563;
        font-weight: 600;
    }
    .form-field input,
    .form-field select,
    .form-field textarea {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 9px 10px;
        font-size: 14px;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
    }
    .error-box {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 12px;
        font-size: 13px;
    }
    .helper {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 12px;
    }
    .table-compact {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }
    .table-compact th,
    .table-compact td {
        border-bottom: 1px solid #f0f0f0;
        text-align: left;
        padding: 10px;
        font-size: 13px;
    }
    .table-compact thead tr {
        background: #f9fafb;
    }
    @media (max-width: 900px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    const flightState = {
        providers: [],
        currentProviderId: null,
    };

    function getToken() {
        return localStorage.getItem('token') || '';
    }

    function headers(hasBody = false) {
        const h = {
            'Authorization': 'Bearer ' + getToken(),
            'Accept': 'application/json'
        };
        if (hasBody) {
            h['Content-Type'] = 'application/json';
        }
        return h;
    }

    async function apiRequest(url, options = {}) {
        const response = await fetch(url, options);
        const text = await response.text();
        const data = text ? JSON.parse(text) : {};
        if (!response.ok) {
            const err = new Error(data.message || 'Request failed');
            err.status = response.status;
            err.errors = data.errors || {};
            throw err;
        }
        return data;
    }

    function formatErrors(errors) {
        const lines = [];
        Object.values(errors || {}).forEach(v => {
            if (Array.isArray(v)) {
                v.forEach(msg => lines.push(msg));
            }
        });
        if (!lines.length) return '';
        return `<div class="error-box">${lines.join('<br>')}</div>`;
    }

    function requireProvider() {
        if (!flightState.currentProviderId) {
            showFlightModal('Select Provider First', '<div class="error-box">Please click Pricing on a provider row before managing years or seasons.</div>');
            return false;
        }
        return true;
    }

    function providerName() {
        const provider = flightState.providers.find(p => p.id === flightState.currentProviderId);
        return provider ? provider.name : 'Selected Provider';
    }

    function toggleNavSection(el) {
        const items = el.nextElementSibling;
        items.style.display = items.style.display === 'none' ? 'block' : 'none';
        const icon = el.querySelector('.toggle-icon');
        icon.style.transform = items.style.display === 'none' ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function openFlightPricingSidebar(providerId) {
        if (providerId !== undefined) {
            flightState.currentProviderId = providerId;
        }
        document.getElementById('flight-pricing-sidebar').classList.add('open');
        document.getElementById('flight-selected-provider').textContent = flightState.currentProviderId
            ? `Provider: ${providerName()}`
            : 'No provider selected';
    }

    function closeFlightSidebar() {
        document.getElementById('flight-pricing-sidebar').classList.remove('open');
    }

    function showFlightModal(title, html) {
        document.getElementById('flight-modal-title').textContent = title;
        document.getElementById('flight-modal-body').innerHTML = html;
        document.getElementById('flight-modal').style.display = 'flex';
    }

    function closeFlightModal() {
        document.getElementById('flight-modal').style.display = 'none';
    }

    function comingSoon(title) {
        showFlightModal(title, '<p class="helper">This section is ready for next-step implementation. Years and Seasons are fully active now.</p>');
    }

    function openFlightRateTypesView() { comingSoon('Flight Rate Types'); }
    function openFlightScheduleView() { comingSoon('Flight Rate Schedule'); }
    function openFlightCharterView() { comingSoon('Flight Charter Rates'); }
    function openFlightChildPolicyView() { comingSoon('Flight Child Policy'); }
    function openFlightPaymentPolicyView() { comingSoon('Flight Payment Policy'); }
    function openFlightCancellationPolicyView() { comingSoon('Flight Cancellation Policy'); }
    function openAddRateTypeForm() { openFlightRateTypesView(); }
    function openAddScheduleForm() { openFlightScheduleView(); }
    function openAddCharterForm() { openFlightCharterView(); }
    function openAddChildPolicyForm() { openFlightChildPolicyView(); }
    function openAddPaymentPolicyForm() { openFlightPaymentPolicyView(); }
    function openAddCancellationPolicyForm() { openFlightCancellationPolicyView(); }

    async function openFlightYearsView() {
        if (!requireProvider()) return;
        try {
            const years = await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/years`, {
                headers: headers()
            });
            const rows = years.length ? years.map(y => `
                <tr>
                    <td>${y.year}</td>
                    <td>${y.valid_from}</td>
                    <td>${y.valid_to}</td>
                    <td>${y.status}</td>
                    <td>${y.season_count}</td>
                    <td>
                        <button class="action-btn" onclick="openFlightSeasonsView(${y.id})">Seasons</button>
                        <button class="action-btn" onclick="deleteFlightYear(${y.id})">Delete</button>
                    </td>
                </tr>
            `).join('') : '<tr><td colspan="6">No years found.</td></tr>';

            showFlightModal(`Years - ${providerName()}`, `
                <p class="helper">Manage rate years and jump directly into each year's seasons.</p>
                <button class="btn btn-primary" onclick="openAddYearForm()">+ Add Year</button>
                <table class="table-compact">
                    <thead><tr><th>Year</th><th>Valid From</th><th>Valid To</th><th>Status</th><th>Seasons</th><th>Actions</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            `);
        } catch (err) {
            showFlightModal('Years', `<div class="error-box">${err.message}</div>`);
        }
    }

    function openAddYearForm() {
        if (!requireProvider()) return;
        showFlightModal(`Add Year - ${providerName()}`, `
            <form id="flight-add-year-form">
                <div id="flight-add-year-errors"></div>
                <div class="form-grid">
                    <div class="form-field"><label>Year</label><input type="number" name="year" min="2020" max="2099" required></div>
                    <div class="form-field"><label>Status</label>
                        <select name="status"><option value="draft">draft</option><option value="active">active</option><option value="archived">archived</option></select>
                    </div>
                    <div class="form-field"><label>Valid From</label><input type="date" name="valid_from" required></div>
                    <div class="form-field"><label>Valid To</label><input type="date" name="valid_to" required></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="openFlightYearsView()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Year</button>
                </div>
            </form>
        `);

        document.getElementById('flight-add-year-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = new FormData(e.target);
            const payload = Object.fromEntries(form.entries());
            try {
                await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/years`, {
                    method: 'POST',
                    headers: headers(true),
                    body: JSON.stringify(payload)
                });
                await openFlightYearsView();
            } catch (err) {
                document.getElementById('flight-add-year-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
            }
        });
    }

    async function openFlightSeasonsView(selectedYearId) {
        if (!requireProvider()) return;
        try {
            const years = await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/years`, {
                headers: headers()
            });

            if (!years.length) {
                showFlightModal('Seasons', '<div class="error-box">Create at least one year before adding seasons.</div>');
                return;
            }

            const yearOptions = years.map(y => `<option value="${y.id}">${y.year} (${y.status})</option>`).join('');
            showFlightModal(`Seasons - ${providerName()}`, `
                <div class="form-grid" style="margin-bottom:10px;">
                    <div class="form-field">
                        <label>Year</label>
                        <select id="flight-year-select">${yearOptions}</select>
                    </div>
                </div>
                <div class="form-actions" style="justify-content:flex-start;margin-top:0;">
                    <button class="btn btn-primary" onclick="openAddSeasonForm()">+ Add Season</button>
                </div>
                <div id="flight-seasons-table"></div>
            `);

            const select = document.getElementById('flight-year-select');
            if (selectedYearId) {
                select.value = String(selectedYearId);
            }
            select.addEventListener('change', () => loadFlightSeasons(select.value));
            await loadFlightSeasons(select.value);
        } catch (err) {
            showFlightModal('Seasons', `<div class="error-box">${err.message}</div>`);
        }
    }

    async function loadFlightSeasons(yearId) {
        const target = document.getElementById('flight-seasons-table');
        target.innerHTML = '<p class="helper">Loading seasons...</p>';
        try {
            const seasons = await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/years/${yearId}/seasons`, {
                headers: headers()
            });
            const rows = seasons.length ? seasons.map(s => `
                <tr>
                    <td>${s.name}</td>
                    <td>${s.start_date}</td>
                    <td>${s.end_date}</td>
                    <td>${s.duration_days}</td>
                    <td><button class="action-btn" onclick="deleteFlightSeason(${s.id}, ${yearId})">Delete</button></td>
                </tr>
            `).join('') : '<tr><td colspan="5">No seasons found for this year.</td></tr>';

            target.innerHTML = `
                <table class="table-compact">
                    <thead><tr><th>Name</th><th>Start</th><th>End</th><th>Days</th><th>Actions</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            `;
        } catch (err) {
            target.innerHTML = `<div class="error-box">${err.message}</div>`;
        }
    }

    async function openAddSeasonForm() {
        if (!requireProvider()) return;
        try {
            const years = await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/years`, {
                headers: headers()
            });
            if (!years.length) {
                showFlightModal('Add Season', '<div class="error-box">Create a year first.</div>');
                return;
            }
            const options = years.map(y => `<option value="${y.id}">${y.year}</option>`).join('');
            showFlightModal(`Add Season - ${providerName()}`, `
                <form id="flight-add-season-form">
                    <div id="flight-add-season-errors"></div>
                    <div class="form-grid">
                        <div class="form-field"><label>Year</label><select name="year_id" required>${options}</select></div>
                        <div class="form-field"><label>Season Name</label>
                            <select name="name" required><option>Low</option><option>High</option><option>Peak</option></select>
                        </div>
                        <div class="form-field"><label>Start Date</label><input type="date" name="start_date" required></div>
                        <div class="form-field"><label>End Date</label><input type="date" name="end_date" required></div>
                        <div class="form-field"><label>Display Order</label><input type="number" name="display_order" min="0" value="0"></div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="openFlightSeasonsView(document.querySelector('#flight-add-season-form [name=year_id]').value)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Season</button>
                    </div>
                </form>
            `);

            document.getElementById('flight-add-season-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = new FormData(e.target);
                const yearId = form.get('year_id');
                const payload = {
                    name: form.get('name'),
                    start_date: form.get('start_date'),
                    end_date: form.get('end_date'),
                    display_order: form.get('display_order')
                };
                try {
                    await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/years/${yearId}/seasons`, {
                        method: 'POST',
                        headers: headers(true),
                        body: JSON.stringify(payload)
                    });
                    await openFlightSeasonsView(yearId);
                } catch (err) {
                    document.getElementById('flight-add-season-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
                }
            });
        } catch (err) {
            showFlightModal('Add Season', `<div class="error-box">${err.message}</div>`);
        }
    }

    async function deleteFlightYear(yearId) {
        if (!confirm('Delete this year?')) return;
        try {
            await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/years/${yearId}`, {
                method: 'DELETE',
                headers: headers()
            });
            await openFlightYearsView();
        } catch (err) {
            showFlightModal('Delete Year', `<div class="error-box">${err.message}</div>`);
        }
    }

    async function deleteFlightSeason(seasonId, yearId) {
        if (!confirm('Delete this season?')) return;
        try {
            await apiRequest(`/api/flight-providers/${flightState.currentProviderId}/pricing/seasons/${seasonId}`, {
                method: 'DELETE',
                headers: headers()
            });
            await loadFlightSeasons(yearId);
        } catch (err) {
            showFlightModal('Delete Season', `<div class="error-box">${err.message}</div>`);
        }
    }

    // Load flight providers on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadFlightProviders();
        const modal = document.getElementById('flight-modal');
        modal.addEventListener('click', function(e) {
            if (e.target.id === 'flight-modal') {
                closeFlightModal();
            }
        });
    });

    function loadFlightProviders() {
        apiRequest('/api/flight-providers', { headers: headers() })
        .then(data => {
            flightState.providers = data;
            const list = document.getElementById('flight-providers-list');
            if (data.length === 0) {
                list.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No flight providers yet</p></div></td></tr>';
                document.getElementById('total-providers').textContent = '0';
                document.getElementById('total-aircraft').textContent = '0';
                document.getElementById('total-routes').textContent = '0';
                document.getElementById('total-rates').textContent = '0';
                return;
            }
            list.innerHTML = data.map((p, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td style="font-weight:600;">${p.name}</td>
                    <td>${p.total_routes || '0'} routes</td>
                    <td>${p.active_seasons || '0'} seasons</td>
                    <td>${p.last_updated}</td>
                    <td>
                        <button class="action-btn" onclick="editFlightProvider(${p.id})">✏️ Edit</button>
                        <button class="action-btn" onclick="openFlightPricingSidebar(${p.id})">💰 Pricing</button>
                        <button class="action-btn" onclick="deleteFlightProvider(${p.id})">🗑️</button>
                    </td>
                </tr>
            `).join('');

            document.getElementById('total-providers').textContent = String(data.length);
            document.getElementById('total-routes').textContent = String(data.reduce((acc, item) => acc + Number(item.total_routes || 0), 0));
            document.getElementById('total-aircraft').textContent = '-';
            document.getElementById('total-rates').textContent = '-';
        })
        .catch(err => {
            document.getElementById('flight-providers-list').innerHTML = `<tr><td colspan="6"><div class="error-box">${err.message}</div></td></tr>`;
        });
    }

    function showAddFlightModal() {
        showFlightModal('Add Flight Provider', `
            <form id="flight-provider-form">
                <div id="flight-provider-errors"></div>
                <div class="form-grid">
                    <div class="form-field"><label>Name</label><input name="name" required></div>
                    <div class="form-field"><label>Email</label><input name="email" type="email"></div>
                    <div class="form-field"><label>Phone</label><input name="phone"></div>
                    <div class="form-field"><label>Contact Person</label><input name="contact_person"></div>
                    <div class="form-field"><label>Markup (%)</label><input name="markup" type="number" min="0" step="0.01"></div>
                    <div class="form-field"><label>VAT Type</label>
                        <select name="vat_type"><option value="inclusive">inclusive</option><option value="exclusive">exclusive</option></select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeFlightModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Provider</button>
                </div>
            </form>
        `);

        document.getElementById('flight-provider-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const payload = Object.fromEntries(new FormData(e.target).entries());
            try {
                await apiRequest('/api/flight-providers', {
                    method: 'POST',
                    headers: headers(true),
                    body: JSON.stringify(payload)
                });
                closeFlightModal();
                await loadFlightProviders();
            } catch (err) {
                document.getElementById('flight-provider-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
            }
        });
    }

    function editFlightProvider(id) {
        showFlightModal('Edit Flight Provider', '<p class="helper">Edit form can be added next using the same modal flow.</p>');
    }

    async function deleteFlightProvider(id) {
        if (!confirm('Delete this flight provider?')) return;
        try {
            await apiRequest(`/api/flight-providers/${id}`, {
                method: 'DELETE',
                headers: headers()
            });
            if (flightState.currentProviderId === id) {
                flightState.currentProviderId = null;
            }
            await loadFlightProviders();
        } catch (err) {
            showFlightModal('Delete Provider', `<div class="error-box">${err.message}</div>`);
        }
    }
</script>
@endsection
