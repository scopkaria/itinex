@extends('layouts.app')
@section('title', 'Miscellaneous — Itinex')
@section('body')
<div class="app-wrapper">
    @include('partials.sidebar', ['activePage' => 'miscellaneous'])
    <div class="main-content">
        <header class="topbar">
            <h2 style="font-size:20px;font-weight:700;">Miscellaneous</h2>
            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="logout-form">@csrf<button type="submit">Logout</button></form>
            </div>
        </header>
        <div class="content-area">
            @if(session('success'))<div class="toast toast-success">{{ session('success') }}</div>@endif

            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="Search items..." onkeyup="filterItems()">
                <select id="catFilter" onchange="filterItems()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)<option value="{{ $cat }}">{{ ucfirst($cat) }}</option>@endforeach
                </select>
                <button class="btn btn-primary btn-sm" onclick="openDrawer('addDrawer')">+ Add Item</button>
            </div>

            <table class="data-table" id="itemsTable">
                <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Unit</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                    <tr data-name="{{ strtolower($item->name) }}" data-cat="{{ $item->category }}">
                        <td style="font-weight:600;">{{ $item->name }}</td>
                        <td><span class="badge badge-gray">{{ ucfirst($item->category ?? 'General') }}</span></td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>{{ str_replace('_', ' ', $item->unit ?? 'per_person') }}</td>
                        <td>
                            <form method="POST" action="{{ url('/miscellaneous/' . $item->id . '/toggle') }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="badge {{ $item->is_active ? 'badge-green' : 'badge-gray' }}" style="border:none;cursor:pointer;">{{ $item->is_active ? 'Active' : 'Inactive' }}</button>
                            </form>
                        </td>
                        <td class="action-cell">
                            <button class="btn btn-outline btn-xs" onclick="openEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->category }}', {{ $item->price }}, '{{ $item->unit }}', '{{ addslashes($item->description ?? '') }}')">Edit</button>
                            <form method="POST" action="{{ url('/miscellaneous/' . $item->id) }}" onsubmit="return confirm('Delete this item?')" style="display:inline;">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Add Drawer --}}
            <div class="drawer-overlay" id="addDrawer-overlay" onclick="closeDrawer('addDrawer')"></div>
            <div class="side-drawer" id="addDrawer">
                <div class="drawer-header"><h3>Add Miscellaneous Item</h3><button onclick="closeDrawer('addDrawer')">&times;</button></div>
                <div class="drawer-body">
                    <form method="POST" action="{{ url('/miscellaneous') }}">
                        @csrf
                        <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                        <div class="form-group"><label>Category *</label>
                            <select name="category" required>
                                <option value="">Select</option>
                                @foreach(['food','beverage','service','equipment','fee','other'] as $c)<option value="{{ $c }}">{{ ucfirst($c) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Price ($) *</label><input type="number" name="price" step="0.01" required></div>
                        <div class="form-group"><label>Unit</label>
                            <select name="unit">
                                <option value="per_person">Per Person</option><option value="per_group">Per Group</option><option value="flat">Flat</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
                        <div class="drawer-footer"><button type="submit" class="btn btn-primary">Save</button></div>
                    </form>
                </div>
            </div>

            {{-- Edit Drawer --}}
            <div class="drawer-overlay" id="editDrawer-overlay" onclick="closeDrawer('editDrawer')"></div>
            <div class="side-drawer" id="editDrawer">
                <div class="drawer-header"><h3>Edit Item</h3><button onclick="closeDrawer('editDrawer')">&times;</button></div>
                <div class="drawer-body">
                    <form method="POST" id="editForm">
                        @csrf @method('PUT')
                        <div class="form-group"><label>Name *</label><input type="text" name="name" id="editName" required></div>
                        <div class="form-group"><label>Category *</label>
                            <select name="category" id="editCategory" required>
                                @foreach(['food','beverage','service','equipment','fee','other'] as $c)<option value="{{ $c }}">{{ ucfirst($c) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Price ($) *</label><input type="number" name="price" id="editPrice" step="0.01" required></div>
                        <div class="form-group"><label>Unit</label>
                            <select name="unit" id="editUnit">
                                <option value="per_person">Per Person</option><option value="per_group">Per Group</option><option value="flat">Flat</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Description</label><textarea name="description" id="editDesc" rows="3"></textarea></div>
                        <div class="drawer-footer"><button type="submit" class="btn btn-primary">Update</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function filterItems(){
    const s=document.getElementById('searchInput').value.toLowerCase();
    const c=document.getElementById('catFilter').value;
    document.querySelectorAll('#itemsTable tbody tr').forEach(r=>{
        const nm=r.dataset.name, cat=r.dataset.cat;
        r.style.display=(nm.includes(s))&&(!c||cat===c)?'':'none';
    });
}
function openEdit(id,name,cat,price,unit,desc){
    document.getElementById('editForm').action='/miscellaneous/'+id;
    document.getElementById('editName').value=name;
    document.getElementById('editCategory').value=cat;
    document.getElementById('editPrice').value=price;
    document.getElementById('editUnit').value=unit||'per_person';
    document.getElementById('editDesc').value=desc;
    openDrawer('editDrawer');
}
</script>
@endsection
