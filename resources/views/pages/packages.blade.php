@extends('layouts.app')
@section('title', 'Packages - Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'packages'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Packages</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>

        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="toast toast-error">{{ $errors->first() }}</div>@endif

            <div class="page-header">
                <h2>Package Catalogue</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ url('/packages/template-csv') }}" class="btn" style="background:#7c3aed;color:#fff;border:none;padding:10px 14px;border-radius:8px;text-decoration:none;">CSV Template</a>
                    <a href="{{ url('/packages/export-csv') }}" class="btn" style="background:#1d4ed8;color:#fff;border:none;padding:10px 14px;border-radius:8px;text-decoration:none;">Export CSV</a>
                    <button class="btn" style="background:#475569;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;" onclick="document.getElementById('modal-import').classList.add('open')">Import CSV</button>
                    <button class="btn btn-primary" onclick="document.getElementById('modal-create').classList.add('open')">+ New Package</button>
                </div>
            </div>

            <div class="card">
                <form method="GET" action="{{ url('/packages') }}" style="display:flex;gap:10px;align-items:end;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--border-light);flex-wrap:wrap;">
                    <div style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Search</label>
                            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, code, notes" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;min-width:220px;">
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Status</label>
                            <select name="status" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;min-width:140px;">
                                <option value="">All</option>
                                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Price Mode</label>
                            <select name="price_mode" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;min-width:150px;">
                                <option value="">All</option>
                                <option value="per_person" {{ ($filters['price_mode'] ?? '') === 'per_person' ? 'selected' : '' }}>Per Person</option>
                                <option value="per_group" {{ ($filters['price_mode'] ?? '') === 'per_group' ? 'selected' : '' }}>Per Group</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">Destination</label>
                            <select name="destination_id" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;min-width:180px;">
                                <option value="">All</option>
                                @foreach($destinations as $d)
                                    <option value="{{ $d->id }}" {{ (string) ($filters['destination_id'] ?? '') === (string) $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="{{ url('/packages') }}" class="btn" style="background:#e2e8f0;color:#0f172a;border:none;padding:10px 14px;border-radius:8px;text-decoration:none;">Reset</a>
                        <button type="submit" class="btn" style="background:#0f766e;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;">Filter</button>
                    </div>
                </form>

                <form method="POST" action="{{ url('/packages/bulk') }}" id="bulk-form" style="display:flex;gap:8px;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--border-light);flex-wrap:wrap;">
                    @csrf
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <label style="font-size:13px;font-weight:600;"><input type="checkbox" id="check-all-packages"> Select all</label>
                        <select name="action" required style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;min-width:180px;">
                            <option value="">Bulk action</option>
                            <option value="activate">Activate selected</option>
                            <option value="deactivate">Deactivate selected</option>
                            <option value="duplicate">Duplicate selected</option>
                            <option value="delete">Delete selected</option>
                        </select>
                        <button type="submit" class="btn" style="background:#0f766e;color:#fff;border:none;padding:8px 12px;border-radius:8px;cursor:pointer;">Apply</button>
                    </div>
                    <div style="font-size:12px;color:#64748b;">Bulk actions use the selected rows below.</div>
                </form>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Destination</th>
                                <th>Nights</th>
                                <th>Mode</th>
                                <th>Base</th>
                                <th>Markup</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $p)
                                <tr>
                                    <td><input type="checkbox" class="package-check" name="package_ids[]" value="{{ $p->id }}" form="bulk-form"></td>
                                    <td>{{ $p->id }}</td>
                                    <td colspan="10" style="padding:0;">
                                        <form method="POST" action="{{ url('/packages/' . $p->id) }}" style="display:grid;grid-template-columns:1.35fr .9fr 1fr .55fr .8fr .9fr .75fr .85fr .8fr .8fr auto;gap:8px;align-items:center;padding:10px;">
                                            @csrf @method('PUT')
                                            <input type="text" name="name" value="{{ $p->name }}" required style="padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:140px;">
                                            <input type="text" name="code" value="{{ $p->code }}" maxlength="60" placeholder="Code" style="padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:100px;">
                                            <select name="destination_id" style="padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:130px;">
                                                <option value="">No destination</option>
                                                @foreach($destinations as $d)
                                                    <option value="{{ $d->id }}" {{ $p->destination_id === $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="nights" min="1" max="365" value="{{ $p->nights }}" required style="padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;">
                                            <select name="price_mode" style="padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:110px;">
                                                <option value="per_person" {{ $p->price_mode === 'per_person' ? 'selected' : '' }}>Per Person</option>
                                                <option value="per_group" {{ $p->price_mode === 'per_group' ? 'selected' : '' }}>Per Group</option>
                                            </select>
                                            <div style="display:flex;gap:4px;align-items:center;">
                                                <input type="text" name="currency" value="{{ $p->currency }}" maxlength="3" required style="width:54px;padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;">
                                                <input type="number" name="base_price" step="0.01" min="0" value="{{ $p->base_price }}" required style="flex:1;padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:90px;">
                                            </div>
                                            <input type="number" name="markup_percentage" step="0.01" min="0" max="500" value="{{ $p->markup_percentage }}" style="padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:80px;">
                                            <div style="display:flex;gap:4px;align-items:center;">
                                                <select name="discount_mode" style="width:92px;padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;">
                                                    <option value="none" {{ $p->discount_mode === 'none' ? 'selected' : '' }}>None</option>
                                                    <option value="percent" {{ $p->discount_mode === 'percent' ? 'selected' : '' }}>%</option>
                                                    <option value="fixed" {{ $p->discount_mode === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                </select>
                                                <input type="number" name="discount_value" step="0.01" min="0" value="{{ $p->discount_value }}" style="flex:1;padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:76px;">
                                            </div>
                                            <div style="display:flex;align-items:center;justify-content:center;">
                                                <input type="hidden" name="is_active" value="0">
                                                <label style="font-size:12px;font-weight:600;white-space:nowrap;"><input type="checkbox" name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}> Active</label>
                                            </div>
                                            <input type="text" name="notes" value="{{ $p->notes }}" placeholder="Notes" style="padding:7px 8px;border:1px solid #cbd5e1;border-radius:6px;min-width:120px;">
                                            <div style="display:flex;gap:6px;align-items:center;justify-content:flex-end;white-space:nowrap;">
                                                <button type="submit" class="btn" style="background:#0f766e;color:#fff;border:none;padding:7px 10px;border-radius:6px;cursor:pointer;">Save</button>
                                                <button type="submit" class="btn" formaction="{{ url('/packages/' . $p->id) }}" formmethod="POST" onclick="event.preventDefault(); if (confirm('Delete this package?')) { const form = this.closest('form'); const method = document.createElement('input'); method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE'; form.appendChild(method); form.submit(); }" style="background:#dc2626;color:#fff;border:none;padding:7px 10px;border-radius:6px;cursor:pointer;">Delete</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="12"><div class="empty-state"><div class="empty-icon">&#128230;</div><p>No packages yet</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modal-import">
    <div class="modal" style="max-width:680px;">
        <h3>Import Packages CSV</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px;">Required columns: <strong>name, code, destination, nights, price_mode, base_price, markup_percentage, discount_mode, discount_value, currency, is_active, notes</strong></p>
        <form method="POST" action="{{ url('/packages/import-csv') }}" enctype="multipart/form-data">
            @csrf
            @include('partials.company-selector')
            <div class="form-group"><label>CSV File *</label><input type="file" name="csv_file" accept=".csv,text/csv" required></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal-import').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modal-create">
    <div class="modal" style="max-width:700px;">
        <h3>New Package</h3>
        <form method="POST" action="{{ url('/packages') }}">
            @csrf
            @include('partials.company-selector')
            <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Code</label><input type="text" name="code" maxlength="60"></div>
            <div class="form-group"><label>Destination</label>
                <select name="destination_id">
                    <option value="">-- Optional --</option>
                    @foreach($destinations as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Nights *</label><input type="number" name="nights" min="1" max="365" value="1" required></div>
            <div class="form-group"><label>Price Mode *</label>
                <select name="price_mode" required>
                    <option value="per_person">Per Person</option>
                    <option value="per_group">Per Group</option>
                </select>
            </div>
            <div class="form-group"><label>Base Price *</label><input type="number" name="base_price" step="0.01" min="0" required></div>
            <div class="form-group"><label>Markup (%)</label><input type="number" name="markup_percentage" step="0.01" min="0" max="500" value="0"></div>
            <div class="form-group"><label>Discount Mode *</label>
                <select name="discount_mode" required>
                    <option value="none">None</option>
                    <option value="percent">Percent</option>
                    <option value="fixed">Fixed</option>
                </select>
            </div>
            <div class="form-group"><label>Discount Value</label><input type="number" name="discount_value" step="0.01" min="0" value="0"></div>
            <div class="form-group"><label>Currency *</label><input type="text" name="currency" maxlength="3" value="USD" required></div>
            <div class="form-group"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
            <div class="form-group"><label>Notes</label><textarea name="notes" rows="3"></textarea></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal-create').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('check-all-packages').addEventListener('change', function () {
    document.querySelectorAll('.package-check').forEach(function (checkbox) {
        checkbox.checked = document.getElementById('check-all-packages').checked;
    });
});

document.getElementById('bulk-form').addEventListener('submit', function (event) {
    const selected = document.querySelectorAll('.package-check:checked').length;
    const action = this.querySelector('select[name="action"]').value;

    if (!action || selected === 0) {
        event.preventDefault();
        alert('Select at least one package and a bulk action.');
        return;
    }

    if (!confirm('Apply bulk action to ' + selected + ' package(s)?')) {
        event.preventDefault();
    }
});
</script>
@endsection
