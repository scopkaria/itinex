<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\TransportProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportProviderController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $providers = TransportProvider::where('company_id', $this->companyId($request))
            ->with([
                'vehicleTypes',
                'vehicles',
                'transferRoutes',
            ])
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'operation_type' => $p->description ?? 'Hired',
                'vehicle_types' => $p->vehicleTypes->pluck('name')->join(', '),
                'last_updated' => $p->updated_at->format('M d, Y'),
                'is_active' => $p->is_active,
            ]);

        return response()->json($providers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vat_type' => ['nullable', 'in:inclusive,exclusive'],
            'markup' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $data['is_active'] = true;

        $provider = TransportProvider::create($data);

        return response()->json([
            'id' => $provider->id,
            'name' => $provider->name,
            'message' => 'Transport provider created successfully.',
        ], 201);
    }

    public function show(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        return response()->json([
            'id' => $provider->id,
            'name' => $provider->name,
            'email' => $provider->email,
            'phone' => $provider->phone,
            'contact_person' => $provider->contact_person,
            'description' => $provider->description,
            'vat_type' => $provider->vat_type,
            'markup' => $provider->markup,
            'is_active' => $provider->is_active,
        ]);
    }

    public function update(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vat_type' => ['nullable', 'in:inclusive,exclusive'],
            'markup' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $provider->update($data);

        return response()->json(['message' => 'Transport provider updated successfully.']);
    }

    public function destroy(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        $provider->delete();

        return response()->json(['message' => 'Transport provider deleted successfully.']);
    }

    private function authorizeCompany(Request $request, TransportProvider $provider): void
    {
        if ($provider->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
