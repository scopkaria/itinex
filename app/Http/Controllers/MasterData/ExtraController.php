<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\Extra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtraController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $extras = Extra::where('company_id', $this->companyId($request))
            ->orderBy('name')
            ->get();

        return response()->json($extras);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $extra = Extra::create($data);

        return response()->json($extra, 201);
    }

    public function update(Request $request, Extra $extra): JsonResponse
    {
        $this->authorizeCompany($request, $extra);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $extra->update($data);

        return response()->json($extra);
    }

    public function destroy(Request $request, Extra $extra): JsonResponse
    {
        $this->authorizeCompany($request, $extra);
        $extra->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function authorizeCompany(Request $request, Extra $extra): void
    {
        if ($extra->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
