<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\AircraftType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlightAircraftController extends Controller
{
    use TenantScoped;

    public function index(Request $request, FlightProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $aircraft = $provider->aircraftTypes()
            ->orderBy('name')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'seats' => $a->capacity,
                'type' => $a->description ? 'info' : null,
                'status' => 'Active',
            ]);

        return response()->json($aircraft);
    }

    public function store(Request $request, FlightProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
        ]);

        $data['flight_provider_id'] = $provider->id;

        $aircraft = AircraftType::create($data);

        return response()->json([
            'id' => $aircraft->id,
            'name' => $aircraft->name,
            'seats' => $aircraft->capacity,
            'message' => 'Aircraft type created successfully.',
        ], 201);
    }

    public function update(Request $request, FlightProvider $provider, AircraftType $aircraft): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($aircraft->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:999'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $aircraft->update($data);

        return response()->json(['message' => 'Aircraft type updated successfully.']);
    }

    public function destroy(Request $request, FlightProvider $provider, AircraftType $aircraft): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($aircraft->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $aircraft->delete();

        return response()->json(['message' => 'Aircraft type deleted successfully.']);
    }

    private function authorizeCompany(Request $request, FlightProvider $provider): void
    {
        if ($provider->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
