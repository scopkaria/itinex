@extends('layouts.app')
@section('title', 'Transport Module — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'transport'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Transport Module</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <!-- TRANSPORT DASHBOARD HEADER -->
            <div class="page-header">
                <h1>Transport Providers Management</h1>
                <div class="action-group">
                    <button class="btn btn-secondary" onclick="showAddTransportProviderModal()">+ Add Transport Provider</button>
                </div>
            </div>

            <!-- MAIN DASHBOARD TABLE -->
            <div class="card">
                <div class="card-header">
                    <h3>Transport Providers Overview</h3>
                    <p style="color:#666;font-size:14px;margin:8px 0 0 0;">Manage vehicles, drivers, routes, and pricing</p>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:5%;">#</th>
                                <th style="width:25%;">Company Name</th>
                                <th style="width:20%;">Operation Type</th>
                                <th style="width:15%;">Vehicle Types</th>
                                <th style="width:15%;">Last Updated</th>
                                <th style="width:20%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transport-providers-list">
                            <tr><td colspan="6"><div class="loading">Loading transport providers...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- QUICK STATS SECTION -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 24px;">
                <div class="stat-card">
                    <div class="stat-value" id="total-providers">0</div>
                    <div class="stat-label">Transport Providers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="total-vehicles">0</div>
                    <div class="stat-label">Vehicles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="total-drivers">0</div>
                    <div class="stat-label">Drivers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="total-routes">0</div>
                    <div class="stat-label">Transfer Routes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- SIDEBAR NAVIGATION FOR TRANSPORT SETTINGS -->
    <aside class="transport-settings-sidebar" id="transport-settings-sidebar">
        <div class="sidebar-header">
            <div>
                <h3>Transport Settings</h3>
                <p id="transport-selected-provider-settings" style="margin:6px 0 0 0;color:#666;font-size:12px;">No provider selected</p>
            </div>
            <button class="close-btn" onclick="closeTransportSidebar()">&times;</button>
        </div>

        <div class="sidebar-nav">
            <!-- SECTION A: VEHICLE SETUP -->
            <div class="sidebar-section-title">🚗 Vehicle Setup</div>

            <!-- Vehicle Types -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">🚙</span> Vehicle Types
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openVehicleTypesView()">📋 All Types</button>
                    <button class="nav-item" onclick="openAddVehicleTypeForm()">➕ Add Type</button>
                </div>
            </div>

            <!-- Vehicles -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">🚌</span> Vehicles
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openVehiclesView()">📋 All Vehicles</button>
                    <button class="nav-item" onclick="openAddVehicleForm()">➕ Add Vehicle</button>
                </div>
            </div>

            <!-- Drivers -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">👤</span> Drivers
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openDriversView()">📋 All Drivers</button>
                    <button class="nav-item" onclick="openAddDriverForm()">➕ Add Driver</button>
                </div>
            </div>

            <!-- SECTION B: ROUTES -->
            <div class="sidebar-section-title">🛣️ Routes (Transfer Points)</div>

            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">📍</span> Transfer Routes
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openTransferRoutesView()">📋 All Routes</button>
                    <button class="nav-item" onclick="openAddTransferRouteForm()">➕ Add Route</button>
                </div>
            </div>

            <!-- SECTION C: DESCRIPTIONS -->
            <div class="sidebar-section-title">📸 Vehicle Descriptions</div>

            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">📝</span> Descriptions
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openVehicleDescriptionsView()">📋 Images & Descriptions</button>
                    <button class="nav-item" onclick="openAddDescriptionForm()">➕ Add Description</button>
                </div>
            </div>

            <!-- SECTION D: IMPREST COMPONENTS -->
            <div class="sidebar-section-title">💵 Cost Settings</div>

            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">⚙️</span> Imprest Components
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openImpresetComponentsView()">📋 Cost Components</button>
                    <button class="nav-item" onclick="openAddImpresetComponentForm()">➕ Add Component</button>
                </div>
            </div>
        </div>

        <div class="sidebar-footer">
            <button class="btn btn-secondary" style="width:100%;" onclick="closeTransportSidebar()">Close</button>
        </div>
    </aside>

    <!-- SIDEBAR FOR TRANSPORT PRICING ENGINE -->
    <aside class="transport-pricing-sidebar" id="transport-pricing-sidebar">
        <div class="sidebar-header">
            <div>
                <h3>Transport Pricing</h3>
                <p id="transport-selected-provider-pricing" style="margin:6px 0 0 0;color:#666;font-size:12px;">No provider selected</p>
            </div>
            <button class="close-btn" onclick="closeTransportPricingSidebar()">&times;</button>
        </div>

        <div class="sidebar-nav">
            <!-- YEARS -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">📅</span> Years
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openTransportYearsView()">📋 Manage Years</button>
                    <button class="nav-item" onclick="openAddTransportYearForm()">➕ Add Year</button>
                </div>
            </div>

            <!-- SEASONS -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">🌤️</span> Seasons
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openTransportSeasonsView()">📋 All Seasons</button>
                    <button class="nav-item" onclick="openAddTransportSeasonForm()">➕ Add Season</button>
                </div>
            </div>

            <!-- RATE TYPES -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">💰</span> Rate Types
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openTransportRateTypesView()">📋 STO / Contract / Custom</button>
                    <button class="nav-item" onclick="openAddTransportRateTypeForm()">➕ Add Rate Type</button>
                </div>
            </div>

            <!-- TRANSFER RATES -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">📊</span> Transfer Rates
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openTransferRatesMatrixView()">📋 Route × Vehicle Matrix</button>
                    <button class="nav-item" onclick="openAddTransferRateForm()">➕ Add Rate</button>
                </div>
            </div>

            <!-- EMPTY RUN RATES -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">🚗</span> Empty Run Rates
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openEmptyRunRatesView()">📋 Empty Runs</button>
                    <button class="nav-item" onclick="openAddEmptyRunRateForm()">➕ Add Empty Run</button>
                </div>
            </div>

            <!-- FUEL SETTINGS -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">⛽</span> Fuel Settings
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openFuelSettingsView()">⚙️ Configure Fuel</button>
                </div>
            </div>

            <!-- PAYMENT POLICY -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">💳</span> Payment Policy
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openTransportPaymentPolicyView()">📋 Payment Terms</button>
                    <button class="nav-item" onclick="openAddTransportPaymentPolicyForm()">➕ Add Policy</button>
                </div>
            </div>

            <!-- CANCELLATION POLICY -->
            <div class="nav-section">
                <h4 class="nav-title" onclick="toggleNavSection(this)">
                    <span class="icon">❌</span> Cancellation Policy
                    <span class="toggle-icon">▼</span>
                </h4>
                <div class="nav-items" style="display:none;">
                    <button class="nav-item" onclick="openTransportCancellationPolicyView()">📋 Cancellation Terms</button>
                    <button class="nav-item" onclick="openAddTransportCancellationPolicyForm()">➕ Add Policy</button>
                </div>
            </div>
        </div>

        <div class="sidebar-footer">
            <button class="btn btn-secondary" style="width:100%;" onclick="closeTransportPricingSidebar()">Close</button>
        </div>
    </aside>

    <div class="modal-backdrop" id="transport-modal" style="display:none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="transport-modal-title">Modal</h3>
                <button class="close-btn" onclick="closeTransportModal()">&times;</button>
            </div>
            <div class="modal-body" id="transport-modal-body"></div>
        </div>
    </div>
</div>

<style>
    .app-wrapper { display: flex; }
    .main-content { flex: 1; }
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
    .transport-settings-sidebar,
    .transport-pricing-sidebar {
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
    .transport-settings-sidebar.open,
    .transport-pricing-sidebar.open {
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
    .sidebar-section-title {
        padding: 16px 16px 8px;
        font-size: 12px;
        font-weight: 700;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
        width: min(1050px, 100%);
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
        vertical-align: top;
    }
    .table-compact thead tr {
        background: #f9fafb;
    }
    .matrix-toolbar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        align-items: end;
    }
    @media (max-width: 900px) {
        .form-grid,
        .matrix-toolbar {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    const transportState = {
        providers: [],
        currentProviderId: null,
        matrixCache: {
            rates: [],
            routes: [],
            vehicleTypes: [],
            seasons: []
        }
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

    function currentProviderName() {
        const provider = transportState.providers.find(p => p.id === transportState.currentProviderId);
        return provider ? provider.name : 'Selected Provider';
    }

    function requireProvider() {
        if (!transportState.currentProviderId) {
            showTransportModal('Select Provider First', '<div class="error-box">Please click Settings or Pricing on a provider row first.</div>');
            return false;
        }
        return true;
    }

    function toggleNavSection(el) {
        const items = el.nextElementSibling;
        items.style.display = items.style.display === 'none' ? 'block' : 'none';
        const icon = el.querySelector('.toggle-icon');
        icon.style.transform = items.style.display === 'none' ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function setTransportProvider(providerId) {
        if (providerId !== undefined) {
            transportState.currentProviderId = providerId;
        }
        const label = transportState.currentProviderId ? `Provider: ${currentProviderName()}` : 'No provider selected';
        document.getElementById('transport-selected-provider-settings').textContent = label;
        document.getElementById('transport-selected-provider-pricing').textContent = label;
    }

    function openTransportSettingsSidebar(providerId) {
        setTransportProvider(providerId);
        document.getElementById('transport-settings-sidebar').classList.add('open');
    }

    function openTransportPricingSidebar(providerId) {
        setTransportProvider(providerId);
        document.getElementById('transport-pricing-sidebar').classList.add('open');
    }

    function closeTransportSidebar() {
        document.getElementById('transport-settings-sidebar').classList.remove('open');
    }

    function closeTransportPricingSidebar() {
        document.getElementById('transport-pricing-sidebar').classList.remove('open');
    }

    function showTransportModal(title, html) {
        document.getElementById('transport-modal-title').textContent = title;
        document.getElementById('transport-modal-body').innerHTML = html;
        document.getElementById('transport-modal').style.display = 'flex';
    }

    function closeTransportModal() {
        document.getElementById('transport-modal').style.display = 'none';
    }

    function comingSoon(title) {
        showTransportModal(title, '<p class="helper">This section is ready for next-step implementation. Years, Seasons, and Transfer Rate Matrix are active now.</p>');
    }

    function openVehicleTypesView() { comingSoon('Vehicle Types'); }
    function openVehiclesView() { comingSoon('Vehicles'); }
    function openDriversView() { comingSoon('Drivers'); }
    function openTransferRoutesView() { comingSoon('Transfer Routes'); }
    function openVehicleDescriptionsView() { comingSoon('Vehicle Descriptions'); }
    function openImpresetComponentsView() { comingSoon('Imprest Components'); }
    function openAddVehicleTypeForm() { openVehicleTypesView(); }
    function openAddVehicleForm() { openVehiclesView(); }
    function openAddDriverForm() { openDriversView(); }
    function openAddTransferRouteForm() { openTransferRoutesView(); }
    function openAddDescriptionForm() { openVehicleDescriptionsView(); }
    function openAddImpresetComponentForm() { openImpresetComponentsView(); }

    function openTransportRateTypesView() { comingSoon('Transport Rate Types'); }
    function openEmptyRunRatesView() { comingSoon('Empty Run Rates'); }
    function openFuelSettingsView() { comingSoon('Fuel Settings'); }
    function openTransportPaymentPolicyView() { comingSoon('Transport Payment Policy'); }
    function openTransportCancellationPolicyView() { comingSoon('Transport Cancellation Policy'); }
    function openAddTransportRateTypeForm() { openTransportRateTypesView(); }
    function openAddEmptyRunRateForm() { openEmptyRunRatesView(); }
    function openAddTransportPaymentPolicyForm() { openTransportPaymentPolicyView(); }
    function openAddTransportCancellationPolicyForm() { openTransportCancellationPolicyView(); }

    async function openTransportYearsView() {
        if (!requireProvider()) return;
        try {
            const years = await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years`, {
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
                        <button class="action-btn" onclick="openTransportSeasonsView(${y.id})">Seasons</button>
                        <button class="action-btn" onclick="deleteTransportYear(${y.id})">Delete</button>
                    </td>
                </tr>
            `).join('') : '<tr><td colspan="6">No years found.</td></tr>';

            showTransportModal(`Years - ${currentProviderName()}`, `
                <p class="helper">Manage rate years and jump directly into each year's seasons.</p>
                <button class="btn btn-primary" onclick="openAddTransportYearForm()">+ Add Year</button>
                <table class="table-compact">
                    <thead><tr><th>Year</th><th>Valid From</th><th>Valid To</th><th>Status</th><th>Seasons</th><th>Actions</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            `);
        } catch (err) {
            showTransportModal('Years', `<div class="error-box">${err.message}</div>`);
        }
    }

    function openAddTransportYearForm() {
        if (!requireProvider()) return;
        showTransportModal(`Add Year - ${currentProviderName()}`, `
            <form id="transport-add-year-form">
                <div id="transport-add-year-errors"></div>
                <div class="form-grid">
                    <div class="form-field"><label>Year</label><input type="number" name="year" min="2020" max="2099" required></div>
                    <div class="form-field"><label>Status</label>
                        <select name="status"><option value="draft">draft</option><option value="active">active</option><option value="archived">archived</option></select>
                    </div>
                    <div class="form-field"><label>Valid From</label><input type="date" name="valid_from" required></div>
                    <div class="form-field"><label>Valid To</label><input type="date" name="valid_to" required></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="openTransportYearsView()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Year</button>
                </div>
            </form>
        `);

        document.getElementById('transport-add-year-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const payload = Object.fromEntries(new FormData(e.target).entries());
            try {
                await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years`, {
                    method: 'POST',
                    headers: headers(true),
                    body: JSON.stringify(payload)
                });
                await openTransportYearsView();
            } catch (err) {
                document.getElementById('transport-add-year-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
            }
        });
    }

    async function openTransportSeasonsView(selectedYearId) {
        if (!requireProvider()) return;
        try {
            const years = await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years`, {
                headers: headers()
            });
            if (!years.length) {
                showTransportModal('Seasons', '<div class="error-box">Create at least one year before adding seasons.</div>');
                return;
            }
            const options = years.map(y => `<option value="${y.id}">${y.year} (${y.status})</option>`).join('');
            showTransportModal(`Seasons - ${currentProviderName()}`, `
                <div class="form-grid" style="margin-bottom:10px;">
                    <div class="form-field">
                        <label>Year</label>
                        <select id="transport-year-select">${options}</select>
                    </div>
                </div>
                <div class="form-actions" style="justify-content:flex-start;margin-top:0;">
                    <button class="btn btn-primary" onclick="openAddTransportSeasonForm()">+ Add Season</button>
                </div>
                <div id="transport-seasons-table"></div>
            `);
            const select = document.getElementById('transport-year-select');
            if (selectedYearId) {
                select.value = String(selectedYearId);
            }
            select.addEventListener('change', () => loadTransportSeasons(select.value));
            await loadTransportSeasons(select.value);
        } catch (err) {
            showTransportModal('Seasons', `<div class="error-box">${err.message}</div>`);
        }
    }

    async function loadTransportSeasons(yearId) {
        const target = document.getElementById('transport-seasons-table');
        target.innerHTML = '<p class="helper">Loading seasons...</p>';
        try {
            const seasons = await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years/${yearId}/seasons`, {
                headers: headers()
            });
            const rows = seasons.length ? seasons.map(s => `
                <tr>
                    <td>${s.name}</td>
                    <td>${s.start_date}</td>
                    <td>${s.end_date}</td>
                    <td>${s.duration_days}</td>
                    <td><button class="action-btn" onclick="deleteTransportSeason(${s.id}, ${yearId})">Delete</button></td>
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

    async function openAddTransportSeasonForm() {
        if (!requireProvider()) return;
        try {
            const years = await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years`, {
                headers: headers()
            });
            if (!years.length) {
                showTransportModal('Add Season', '<div class="error-box">Create a year first.</div>');
                return;
            }
            const options = years.map(y => `<option value="${y.id}">${y.year}</option>`).join('');
            showTransportModal(`Add Season - ${currentProviderName()}`, `
                <form id="transport-add-season-form">
                    <div id="transport-add-season-errors"></div>
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
                        <button type="button" class="btn btn-secondary" onclick="openTransportSeasonsView(document.querySelector('#transport-add-season-form [name=year_id]').value)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Season</button>
                    </div>
                </form>
            `);

            document.getElementById('transport-add-season-form').addEventListener('submit', async function(e) {
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
                    await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years/${yearId}/seasons`, {
                        method: 'POST',
                        headers: headers(true),
                        body: JSON.stringify(payload)
                    });
                    await openTransportSeasonsView(yearId);
                } catch (err) {
                    document.getElementById('transport-add-season-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
                }
            });
        } catch (err) {
            showTransportModal('Add Season', `<div class="error-box">${err.message}</div>`);
        }
    }

    async function getAllTransportSeasons() {
        const years = await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years`, {
            headers: headers()
        });
        const seasonGroups = await Promise.all(years.map(y =>
            apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years/${y.id}/seasons`, {
                headers: headers()
            }).then(seasons => seasons.map(s => ({ ...s, year_id: y.id, year: y.year })))
        ));
        return seasonGroups.flat();
    }

    async function openTransferRatesMatrixView() {
        if (!requireProvider()) return;
        try {
            const [rates, routes, vehicleTypes, seasons] = await Promise.all([
                apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/transfer-rates`, { headers: headers() }),
                apiRequest(`/api/transport-providers/${transportState.currentProviderId}/vehicles/routes`, { headers: headers() }),
                apiRequest(`/api/transport-providers/${transportState.currentProviderId}/vehicles/types`, { headers: headers() }),
                getAllTransportSeasons()
            ]);

            transportState.matrixCache = { rates, routes, vehicleTypes, seasons };

            const seasonOptions = ['<option value="">All Seasons</option>']
                .concat(seasons.map(s => `<option value="${s.id}">${s.name} (${s.year})</option>`))
                .join('');

            const routeOptions = routes.map(r => `<option value="${r.id}">${r.from} → ${r.to}</option>`).join('');
            const vehicleOptions = vehicleTypes.map(v => `<option value="${v.id}">${v.name} (${v.capacity || 0} seats)</option>`).join('');
            const seasonSelectForForm = ['<option value="">All Seasons</option>']
                .concat(seasons.map(s => `<option value="${s.id}">${s.name} (${s.year})</option>`))
                .join('');

            showTransportModal(`Transfer Rate Matrix - ${currentProviderName()}`, `
                <p class="helper">Set buy/sell prices by Route × Vehicle Type with optional season override.</p>
                <div class="matrix-toolbar">
                    <div class="form-field">
                        <label>Filter by Season</label>
                        <select id="matrix-season-filter">${seasonOptions}</select>
                    </div>
                </div>

                <form id="add-transfer-rate-form" style="margin-top:14px; border:1px solid #e5e7eb; border-radius:8px; padding:12px;">
                    <div id="add-transfer-rate-errors"></div>
                    <div class="form-grid">
                        <div class="form-field"><label>Route</label><select name="transfer_route_id" required>${routeOptions}</select></div>
                        <div class="form-field"><label>Vehicle Type</label><select name="vehicle_type_id" required>${vehicleOptions}</select></div>
                        <div class="form-field"><label>Season</label><select name="transport_season_id">${seasonSelectForForm}</select></div>
                        <div class="form-field"><label>Buy Price</label><input name="buy_price" type="number" min="0" step="0.01" required></div>
                        <div class="form-field"><label>Sell Price</label><input name="sell_price" type="number" min="0" step="0.01" required></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add Rate</button>
                    </div>
                </form>

                <div id="transport-matrix-table"></div>
            `);

            document.getElementById('matrix-season-filter').addEventListener('change', renderTransferRateTable);
            document.getElementById('add-transfer-rate-form').addEventListener('submit', submitAddTransferRate);
            renderTransferRateTable();
        } catch (err) {
            showTransportModal('Transfer Rate Matrix', `<div class="error-box">${err.message}</div>`);
        }
    }

    function renderTransferRateTable() {
        const filterSeason = document.getElementById('matrix-season-filter')?.value || '';
        const container = document.getElementById('transport-matrix-table');
        if (!container) return;

        const filtered = transportState.matrixCache.rates.filter(r => {
            if (!filterSeason) return true;
            return String(r.transport_season_id || '') === String(filterSeason);
        });

        const rows = filtered.length ? filtered.map(r => `
            <tr>
                <td>${r.route}</td>
                <td>${r.vehicle_type}</td>
                <td>${r.season}</td>
                <td>${Number(r.buy_price).toFixed(2)}</td>
                <td>${Number(r.sell_price).toFixed(2)}</td>
                <td>${Number(r.margin).toFixed(2)}</td>
                <td>
                    <button class="action-btn" onclick="openEditTransferRateForm(${r.id})">Edit</button>
                    <button class="action-btn" onclick="deleteTransferRate(${r.id})">Delete</button>
                </td>
            </tr>
        `).join('') : '<tr><td colspan="7">No rates match the selected filter.</td></tr>';

        container.innerHTML = `
            <table class="table-compact">
                <thead><tr><th>Route</th><th>Vehicle</th><th>Season</th><th>Buy</th><th>Sell</th><th>Margin</th><th>Actions</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>
        `;
    }

    async function submitAddTransferRate(e) {
        e.preventDefault();
        const form = new FormData(e.target);
        const buy = Number(form.get('buy_price'));
        const sell = Number(form.get('sell_price'));
        if (sell < buy) {
            document.getElementById('add-transfer-rate-errors').innerHTML = '<div class="error-box">Sell price should be greater than or equal to buy price.</div>';
            return;
        }

        const payload = {
            transfer_route_id: form.get('transfer_route_id'),
            vehicle_type_id: form.get('vehicle_type_id'),
            transport_season_id: form.get('transport_season_id') || null,
            buy_price: form.get('buy_price'),
            sell_price: form.get('sell_price')
        };

        try {
            await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/transfer-rates`, {
                method: 'POST',
                headers: headers(true),
                body: JSON.stringify(payload)
            });
            await openTransferRatesMatrixView();
        } catch (err) {
            document.getElementById('add-transfer-rate-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
        }
    }

    function openEditTransferRateForm(rateId) {
        const rate = transportState.matrixCache.rates.find(r => r.id === rateId);
        if (!rate) {
            return;
        }
        const seasonOptions = ['<option value="">All Seasons</option>']
            .concat(transportState.matrixCache.seasons.map(s => `<option value="${s.id}">${s.name} (${s.year})</option>`))
            .join('');

        showTransportModal(`Edit Transfer Rate - ${currentProviderName()}`, `
            <div class="helper">${rate.route} | ${rate.vehicle_type}</div>
            <form id="edit-transfer-rate-form">
                <div id="edit-transfer-rate-errors"></div>
                <div class="form-grid">
                    <div class="form-field"><label>Buy Price</label><input type="number" name="buy_price" min="0" step="0.01" value="${rate.buy_price}" required></div>
                    <div class="form-field"><label>Sell Price</label><input type="number" name="sell_price" min="0" step="0.01" value="${rate.sell_price}" required></div>
                    <div class="form-field"><label>Season</label><select name="transport_season_id">${seasonOptions}</select></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="openTransferRatesMatrixView()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        `);

        document.querySelector('#edit-transfer-rate-form [name=transport_season_id]').value = rate.transport_season_id || '';

        document.getElementById('edit-transfer-rate-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = new FormData(e.target);
            const buy = Number(form.get('buy_price'));
            const sell = Number(form.get('sell_price'));
            if (sell < buy) {
                document.getElementById('edit-transfer-rate-errors').innerHTML = '<div class="error-box">Sell price should be greater than or equal to buy price.</div>';
                return;
            }
            const payload = {
                buy_price: form.get('buy_price'),
                sell_price: form.get('sell_price'),
                transport_season_id: form.get('transport_season_id') || null
            };
            try {
                await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/transfer-rates/${rateId}`, {
                    method: 'PUT',
                    headers: headers(true),
                    body: JSON.stringify(payload)
                });
                await openTransferRatesMatrixView();
            } catch (err) {
                document.getElementById('edit-transfer-rate-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
            }
        });
    }

    async function deleteTransferRate(rateId) {
        if (!confirm('Delete this transfer rate?')) return;
        try {
            await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/transfer-rates/${rateId}`, {
                method: 'DELETE',
                headers: headers()
            });
            await openTransferRatesMatrixView();
        } catch (err) {
            showTransportModal('Delete Transfer Rate', `<div class="error-box">${err.message}</div>`);
        }
    }

    async function deleteTransportYear(yearId) {
        if (!confirm('Delete this year?')) return;
        try {
            await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/years/${yearId}`, {
                method: 'DELETE',
                headers: headers()
            });
            await openTransportYearsView();
        } catch (err) {
            showTransportModal('Delete Year', `<div class="error-box">${err.message}</div>`);
        }
    }

    async function deleteTransportSeason(seasonId, yearId) {
        if (!confirm('Delete this season?')) return;
        try {
            await apiRequest(`/api/transport-providers/${transportState.currentProviderId}/pricing/seasons/${seasonId}`, {
                method: 'DELETE',
                headers: headers()
            });
            await loadTransportSeasons(yearId);
        } catch (err) {
            showTransportModal('Delete Season', `<div class="error-box">${err.message}</div>`);
        }
    }

    function openAddTransferRateForm() {
        openTransferRatesMatrixView();
    }

    // Load transport providers on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTransportProviders();
        const modal = document.getElementById('transport-modal');
        modal.addEventListener('click', function(e) {
            if (e.target.id === 'transport-modal') {
                closeTransportModal();
            }
        });
    });

    function loadTransportProviders() {
        apiRequest('/api/transport-providers', { headers: headers() })
        .then(data => {
            transportState.providers = data;
            const list = document.getElementById('transport-providers-list');
            if (data.length === 0) {
                list.innerHTML = '<tr><td colspan="6"><div class="empty-state"><p>No transport providers yet</p></div></td></tr>';
                document.getElementById('total-providers').textContent = '0';
                document.getElementById('total-vehicles').textContent = '0';
                document.getElementById('total-drivers').textContent = '0';
                document.getElementById('total-routes').textContent = '0';
                return;
            }
            list.innerHTML = data.map((p, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td style="font-weight:600;">${p.name}</td>
                    <td>${p.operation_type}</td>
                    <td>${p.vehicle_types || '—'}</td>
                    <td>${p.last_updated}</td>
                    <td>
                        <button class="action-btn" onclick="editTransportProvider(${p.id})">✏️ Edit</button>
                        <button class="action-btn" onclick="openTransportSettingsSidebar(${p.id})">⚙️ Settings</button>
                        <button class="action-btn" onclick="openTransportPricingSidebar(${p.id})">💰 Pricing</button>
                        <button class="action-btn" onclick="deleteTransportProvider(${p.id})">🗑️</button>
                    </td>
                </tr>
            `).join('');

            document.getElementById('total-providers').textContent = String(data.length);
            document.getElementById('total-vehicles').textContent = '-';
            document.getElementById('total-drivers').textContent = '-';
            document.getElementById('total-routes').textContent = '-';
        })
        .catch(err => {
            document.getElementById('transport-providers-list').innerHTML = `<tr><td colspan="6"><div class="error-box">${err.message}</div></td></tr>`;
        });
    }

    function showAddTransportProviderModal() {
        showTransportModal('Add Transport Provider', `
            <form id="transport-provider-form">
                <div id="transport-provider-errors"></div>
                <div class="form-grid">
                    <div class="form-field"><label>Name</label><input name="name" required></div>
                    <div class="form-field"><label>Email</label><input name="email" type="email"></div>
                    <div class="form-field"><label>Phone</label><input name="phone"></div>
                    <div class="form-field"><label>Contact Person</label><input name="contact_person"></div>
                    <div class="form-field"><label>Markup (%)</label><input name="markup" type="number" min="0" step="0.01"></div>
                    <div class="form-field"><label>VAT Type</label>
                        <select name="vat_type"><option value="inclusive">inclusive</option><option value="exclusive">exclusive</option></select>
                    </div>
                    <div class="form-field" style="grid-column: 1 / -1;"><label>Description</label><textarea name="description" rows="2"></textarea></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeTransportModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Provider</button>
                </div>
            </form>
        `);

        document.getElementById('transport-provider-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const payload = Object.fromEntries(new FormData(e.target).entries());
            try {
                await apiRequest('/api/transport-providers', {
                    method: 'POST',
                    headers: headers(true),
                    body: JSON.stringify(payload)
                });
                closeTransportModal();
                await loadTransportProviders();
            } catch (err) {
                document.getElementById('transport-provider-errors').innerHTML = formatErrors(err.errors) || `<div class="error-box">${err.message}</div>`;
            }
        });
    }

    function editTransportProvider(id) {
        showTransportModal('Edit Transport Provider', '<p class="helper">Edit form can be added next using the same modal workflow.</p>');
    }

    async function deleteTransportProvider(id) {
        if (!confirm('Delete this transport provider?')) return;
        try {
            await apiRequest(`/api/transport-providers/${id}`, {
                method: 'DELETE',
                headers: headers()
            });
            if (transportState.currentProviderId === id) {
                transportState.currentProviderId = null;
                setTransportProvider(null);
            }
            await loadTransportProviders();
        } catch (err) {
            showTransportModal('Delete Provider', `<div class="error-box">${err.message}</div>`);
        }
    }
</script>
@endsection
