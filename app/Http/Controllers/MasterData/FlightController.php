<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\Flight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $flights = Flight::where('company_id', $this->companyId($request))
            ->orderBy('name')
            ->get();

        return response()->json($flights);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'price_per_person' => ['required', 'numeric', 'min:0'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $flight = Flight::create($data);

        return response()->json($flight, 201);
    }

    public function update(Request $request, Flight $flight): JsonResponse
    {
        $this->authorizeCompany($request, $flight);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'price_per_person' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $flight->update($data);

        return response()->json($flight);
    }

    public function destroy(Request $request, Flight $flight): JsonResponse
    {
        $this->authorizeCompany($request, $flight);
        $flight->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function authorizeCompany(Request $request, Flight $flight): void
    {
        if ($flight->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
