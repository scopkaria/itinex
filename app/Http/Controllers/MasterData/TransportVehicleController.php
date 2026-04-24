<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\TransportProvider;
use App\Models\MasterData\TransportDriver;
use App\Models\MasterData\ProviderVehicle;
use App\Models\MasterData\TransferRoute;
use App\Models\MasterData\Destination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportVehicleController extends Controller
{
    use TenantScoped;

    public function indexVehicleTypes(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $types = $provider->vehicleTypes()
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'capacity' => $t->capacity,
                'category' => $t->category,
            ]);

        return response()->json($types);
    }

    // ─── VEHICLES ─────────────────────────────────────────────
    public function indexVehicles(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $vehicles = $provider->vehicles()
            ->with('vehicleType')
            ->orderBy('registration_number')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'reg_number' => $v->registration_number,
                'model' => $v->make_model,
                'type' => $v->vehicleType->name,
                'seats' => $v->vehicleType->capacity,
                'status' => $v->status,
            ]);

        return response()->json($vehicles);
    }

    public function storeVehicle(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'registration_number' => ['required', 'string', 'max:50'],
            'vehicle_type_id' => ['required', 'exists:vehicle_types,id'],
            'make_model' => ['nullable', 'string', 'max:255'],
            'year_of_manufacture' => ['nullable', 'integer', 'min:1900', 'max:2099'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'fuel_consumption_kmpl' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
            'scope' => ['nullable', 'in:safari,transfer,both'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['transport_provider_id'] = $provider->id;
        $data['status'] = 'available';
        $data['scope'] ??= 'both';

        $vehicle = ProviderVehicle::create($data);

        return response()->json([
            'id' => $vehicle->id,
            'reg_number' => $vehicle->registration_number,
            'message' => 'Vehicle created successfully.',
        ], 201);
    }

    public function updateVehicle(Request $request, TransportProvider $provider, ProviderVehicle $vehicle): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($vehicle->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'registration_number' => ['sometimes', 'string', 'max:50'],
            'make_model' => ['sometimes', 'string', 'max:255'],
            'year_of_manufacture' => ['sometimes', 'integer', 'min:1900', 'max:2099'],
            'engine_number' => ['sometimes', 'string', 'max:100'],
            'chassis_number' => ['sometimes', 'string', 'max:100'],
            'fuel_type' => ['sometimes', 'string', 'max:50'],
            'fuel_consumption_kmpl' => ['sometimes', 'numeric', 'min:0.1', 'max:50'],
            'scope' => ['sometimes', 'in:safari,transfer,both'],
            'status' => ['sometimes', 'in:available,in_service,maintenance'],
            'notes' => ['sometimes', 'string'],
        ]);

        $vehicle->update($data);

        return response()->json(['message' => 'Vehicle updated successfully.']);
    }

    public function destroyVehicle(Request $request, TransportProvider $provider, ProviderVehicle $vehicle): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($vehicle->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully.']);
    }

    // ─── DRIVERS ──────────────────────────────────────────────
    public function indexDrivers(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $drivers = $provider->drivers()
            ->orderBy('name')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'phone' => $d->phone,
                'license_expiry' => $d->license_expiry,
                'skill_level' => $d->skill_level ?? 'pro',
                'status' => $d->status,
                'languages' => $d->languages ? json_decode($d->languages) : [],
            ]);

        return response()->json($drivers);
    }

    public function storeDriver(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'employment_date' => ['nullable', 'date'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_type' => ['nullable', 'string', 'max:50'],
            'license_expiry' => ['nullable', 'date'],
            'skill_level' => ['nullable', 'in:beginner,pro,expert'],
            'languages' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['transport_provider_id'] = $provider->id;
        $data['status'] = 'active';
        $data['languages'] = $data['languages'] ? json_encode($data['languages']) : null;

        $driver = TransportDriver::create($data);

        return response()->json([
            'id' => $driver->id,
            'name' => $driver->name,
            'message' => 'Driver created successfully.',
        ], 201);
    }

    public function updateDriver(Request $request, TransportProvider $provider, TransportDriver $driver): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($driver->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'date_of_birth' => ['sometimes', 'date'],
            'employment_date' => ['sometimes', 'date'],
            'license_number' => ['sometimes', 'string', 'max:100'],
            'license_type' => ['sometimes', 'string', 'max:50'],
            'license_expiry' => ['sometimes', 'date'],
            'skill_level' => ['sometimes', 'in:beginner,pro,expert'],
            'languages' => ['sometimes', 'array'],
            'status' => ['sometimes', 'in:active,inactive'],
            'notes' => ['sometimes', 'string'],
        ]);

        if (isset($data['languages'])) {
            $data['languages'] = json_encode($data['languages']);
        }

        $driver->update($data);

        return response()->json(['message' => 'Driver updated successfully.']);
    }

    public function destroyDriver(Request $request, TransportProvider $provider, TransportDriver $driver): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($driver->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $driver->delete();

        return response()->json(['message' => 'Driver deleted successfully.']);
    }

    // ─── TRANSFER ROUTES (Link destinations) ───────────────────
    public function indexRoutes(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $routes = $provider->transferRoutes()
            ->with('originDestination', 'arrivalDestination')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'from' => $r->originDestination?->name ?? 'Unknown',
                'to' => $r->arrivalDestination?->name ?? 'Unknown',
                'distance' => $r->distance_km,
                'duration' => $r->duration_minutes ? floor($r->duration_minutes / 60) . 'h ' . ($r->duration_minutes % 60) . 'm' : 'N/A',
            ]);

        return response()->json($routes);
    }

    public function storeRoute(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'origin_destination_id' => ['required', 'exists:destinations,id'],
            'arrival_destination_id' => ['required', 'exists:destinations,id'],
            'distance_km' => ['nullable', 'integer', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['transport_provider_id'] = $provider->id;

        $route = TransferRoute::create($data);

        return response()->json([
            'id' => $route->id,
            'message' => 'Route created successfully.',
        ], 201);
    }

    public function updateRoute(Request $request, TransportProvider $provider, TransferRoute $route): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($route->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'origin_destination_id' => ['sometimes', 'exists:destinations,id'],
            'arrival_destination_id' => ['sometimes', 'exists:destinations,id'],
            'distance_km' => ['sometimes', 'integer', 'min:0'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
        ]);

        $route->update($data);

        return response()->json(['message' => 'Route updated successfully.']);
    }

    public function destroyRoute(Request $request, TransportProvider $provider, TransferRoute $route): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($route->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $route->delete();

        return response()->json(['message' => 'Route deleted successfully.']);
    }

    private function authorizeCompany(Request $request, TransportProvider $provider): void
    {
        if ($provider->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
