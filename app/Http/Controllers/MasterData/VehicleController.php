<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::where('company_id', $this->companyId($request))
            ->orderBy('name')
            ->get();

        return response()->json($vehicles);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $vehicle = Vehicle::create($data);

        return response()->json($vehicle, 201);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeCompany($request, $vehicle);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'price_per_day' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $vehicle->update($data);

        return response()->json($vehicle);
    }

    public function destroy(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeCompany($request, $vehicle);
        $vehicle->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function authorizeCompany(Request $request, Vehicle $vehicle): void
    {
        if ($vehicle->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
