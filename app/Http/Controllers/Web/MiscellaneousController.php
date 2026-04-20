<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MasterData\Extra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MiscellaneousController extends Controller
{
    private function companyId(): ?int
    {
        return Auth::user()->company_id;
    }

    private function isSuperAdmin(): bool
    {
        return Auth::user()->isSuperAdmin();
    }

    private function scopedQuery()
    {
        $query = Extra::query();
        if (!$this->isSuperAdmin()) {
            $query->where('company_id', $this->companyId());
        }
        return $query;
    }

    private function resolveCompanyId(Request $request): int
    {
        if ($this->isSuperAdmin()) {
            return (int) $request->input('company_id');
        }
        return $this->companyId();
    }

    private function companyRules(): array
    {
        return $this->isSuperAdmin()
            ? ['company_id' => 'required|exists:companies,id']
            : [];
    }

    private function companiesForForm(): \Illuminate\Support\Collection
    {
        return $this->isSuperAdmin()
            ? Company::orderBy('name')->get()
            : collect();
    }

    public function index()
    {
        $items = $this->scopedQuery()->orderBy('category')->orderBy('name')->get();
        $companies = $this->companiesForForm();
        $categories = $this->scopedQuery()->whereNotNull('category')->distinct()->pluck('category')->sort()->values();
        return view('pages.miscellaneous', compact('items', 'companies', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|in:per_person,per_group,flat',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        Extra::create($data);
        return back()->with('success', 'Item created.');
    }

    public function update(Request $request, Extra $extra)
    {
        if (!$this->isSuperAdmin() && $extra->company_id !== $this->companyId()) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|in:per_person,per_group,flat',
        ]);
        $extra->update($data);
        return back()->with('success', 'Item updated.');
    }

    public function toggleActive(Extra $extra)
    {
        if (!$this->isSuperAdmin() && $extra->company_id !== $this->companyId()) {
            abort(403);
        }
        $extra->update(['is_active' => !$extra->is_active]);
        return back()->with('success', 'Status updated.');
    }

    public function delete(Extra $extra)
    {
        if (!$this->isSuperAdmin() && $extra->company_id !== $this->companyId()) {
            abort(403);
        }
        $extra->delete();
        return back()->with('success', 'Item deleted.');
    }
}
