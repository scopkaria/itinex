<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $hotels = Hotel::where('company_id', $this->companyId($request))
            ->with(['location', 'roomTypes'])
            ->orderBy('name')
            ->get();

        return response()->json($hotels);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location_id' => ['required', 'exists:destinations,id'],
            'category' => ['required', 'in:budget,midrange,luxury'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $hotel = Hotel::create($data);

        return response()->json($hotel->load(['location', 'roomTypes']), 201);
    }

    public function show(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorizeCompany($request, $hotel);
        return response()->json($hotel->load(['location', 'roomTypes', 'rates.roomType', 'rates.mealPlan']));
    }

    public function update(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorizeCompany($request, $hotel);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'location_id' => ['sometimes', 'exists:destinations,id'],
            'category' => ['sometimes', 'in:budget,midrange,luxury'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $hotel->update($data);

        return response()->json($hotel->load(['location', 'roomTypes']));
    }

    public function destroy(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorizeCompany($request, $hotel);
        $hotel->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function authorizeCompany(Request $request, Hotel $hotel): void
    {
        if ($hotel->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
