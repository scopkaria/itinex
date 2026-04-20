{{-- Company selector for super admin forms --}}
@if(auth()->user()->isSuperAdmin() && isset($companies) && $companies->count())
<div class="form-group">
    <label>Company *</label>
    <select name="company_id" required>
        <option value="">Select company</option>
        @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
    </select>
</div>
@endif
