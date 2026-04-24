<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Hotel;
use App\Services\AccommodationPricingQueryService;
use App\Services\Pricing\RateVisibilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccommodationPricingController extends Controller
{
    public function __construct(
        private readonly AccommodationPricingQueryService $pricingService,
        private readonly RateVisibilityService $rateVisibilityService
    ) {
    }

    public function calculatorBundle(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorizeView($request, $hotel);

        $data = $request->validate([
            'travel_date' => ['required', 'date'],
            'location_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'rate_year_id' => ['nullable', 'integer', 'exists:accommodation_rate_years,id'],
            'room_type_id' => ['nullable', 'integer', 'exists:room_types,id'],
            'meal_plan_id' => ['nullable', 'integer', 'exists:meal_plans,id'],
        ]);

        $bundle = $this->pricingService->calculatorBundle(
            $hotel,
            Carbon::parse($data['travel_date']),
            [
                'location_id' => $data['location_id'] ?? null,
                'rate_year_id' => $data['rate_year_id'] ?? null,
                'room_type_id' => $data['room_type_id'] ?? null,
                'meal_plan_id' => $data['meal_plan_id'] ?? null,
            ]
        );

        $bundle['room_rates'] = $this->rateVisibilityService
            ->sanitizeAccommodationRates($request->user(), $hotel, collect($bundle['room_rates']))
            ->values();

        return response()->json(['data' => $bundle]);
    }

    private function authorizeView(Request $request, Hotel $hotel): void
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isHotel()) {
            $isAssigned = $hotel->owners()->where('users.id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized hotel access.');
            }

            return;
        }

        if ((int) $hotel->company_id !== (int) $user->company_id) {
            abort(403, 'Unauthorized hotel access.');
        }
    }
}
