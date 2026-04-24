<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\AircraftType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FlightProviderController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $providers = FlightProvider::where('company_id', $this->companyId($request))
            ->with([
                'routes' => fn($q) => $q->whereHas('originDestination'),
                'rateYears' => fn($q) => $q->orderBy('year', 'desc'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'phone' => $p->phone,
                'active_seasons' => $p->seasons->count(),
                'last_updated' => $p->updated_at->format('M d, Y'),
                'total_routes' => $p->routes->count(),
                'is_active' => $p->is_active,
            ]);

        return response()->json($providers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vat_type' => ['nullable', 'in:inclusive,exclusive'],
            'markup' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $data['is_active'] = true;

        $provider = FlightProvider::create($data);

        return response()->json([
            'id' => $provider->id,
            'name' => $provider->name,
            'message' => 'Flight provider created successfully.',
        ], 201);
    }

    public function show(Request $request, FlightProvider $flightProvider): JsonResponse
    {
        $this->authorizeCompany($request, $flightProvider);

        return response()->json([
            'id' => $flightProvider->id,
            'name' => $flightProvider->name,
            'email' => $flightProvider->email,
            'phone' => $flightProvider->phone,
            'contact_person' => $flightProvider->contact_person,
            'description' => $flightProvider->description,
            'vat_type' => $flightProvider->vat_type,
            'markup' => $flightProvider->markup,
            'is_active' => $flightProvider->is_active,
            'routes_count' => $flightProvider->routes->count(),
            'aircraft_count' => $flightProvider->aircraftTypes->count(),
        ]);
    }

    public function update(Request $request, FlightProvider $flightProvider): JsonResponse
    {
        $this->authorizeCompany($request, $flightProvider);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vat_type' => ['nullable', 'in:inclusive,exclusive'],
            'markup' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $flightProvider->update($data);

        return response()->json(['message' => 'Flight provider updated successfully.']);
    }

    public function destroy(Request $request, FlightProvider $flightProvider): JsonResponse
    {
        $this->authorizeCompany($request, $flightProvider);
        $flightProvider->delete();

        return response()->json(['message' => 'Flight provider deleted successfully.']);
    }

    private function authorizeCompany(Request $request, FlightProvider $provider): void
    {
        if ($provider->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
