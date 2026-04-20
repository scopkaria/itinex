<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotelRateController extends Controller
{
    use TenantScoped;

    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $rates = $hotel->rates()
            ->where('company_id', $this->companyId($request))
            ->with(['roomType', 'mealPlan'])
            ->orderBy('start_date')
            ->get();

        return response()->json($rates);
    }

    public function store(Request $request, Hotel $hotel): JsonResponse
    {
        $data = $request->validate([
            'season' => ['required', 'in:low,high'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'meal_plan_id' => ['required', 'exists:meal_plans,id'],
            'price_per_person' => ['required', 'numeric', 'min:0'],
        ]);

        $data['hotel_id'] = $hotel->id;
        $data['company_id'] = $this->companyId($request);
        $rate = HotelRate::create($data);

        return response()->json($rate->load(['roomType', 'mealPlan']), 201);
    }

    public function update(Request $request, Hotel $hotel, HotelRate $rate): JsonResponse
    {
        if ($rate->hotel_id !== $hotel->id) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'season' => ['sometimes', 'in:low,high'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'room_type_id' => ['sometimes', 'exists:room_types,id'],
            'meal_plan_id' => ['sometimes', 'exists:meal_plans,id'],
            'price_per_person' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $rate->update($data);

        return response()->json($rate->load(['roomType', 'mealPlan']));
    }

    public function destroy(Hotel $hotel, HotelRate $rate): JsonResponse
    {
        if ($rate->hotel_id !== $hotel->id) {
            abort(403, 'Unauthorized.');
        }

        $rate->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
