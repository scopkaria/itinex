<?php

namespace App\Services;

use App\Models\MasterData\AccommodationRoomRate;
use App\Models\MasterData\Hotel;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AccommodationPricingQueryService
{
    public function calculatorBundle(Hotel $hotel, CarbonInterface $travelDate, array $filters = []): array
    {
        return [
            'hotel_id' => $hotel->id,
            'travel_date' => $travelDate->toDateString(),
            'room_rates' => $this->roomRates($hotel, $travelDate, $filters),
            'extra_fees' => $this->extraFees($hotel, $travelDate),
            'holiday_supplements' => $this->holidaySupplements($hotel, $travelDate, $filters),
            'activities' => $this->activities($hotel),
            'child_policies' => $this->childPolicies($hotel, $travelDate, $filters),
            'payment_policies' => $hotel->paymentPolicies()->orderBy('days_before')->get(),
            'cancellation_policies' => $hotel->cancellationPolicies()->orderByDesc('days_before')->get(),
            'tour_leader_discounts' => $hotel->tourLeaderDiscounts()->orderBy('min_pax')->get(),
        ];
    }

    public function roomRates(Hotel $hotel, CarbonInterface $travelDate, array $filters = []): Collection
    {
        return AccommodationRoomRate::query()
            ->with(['season', 'roomType', 'mealPlan', 'rateType'])
            ->where('hotel_id', $hotel->id)
            ->calculatorApproved()
            ->whereHas('season', function ($query) use ($travelDate, $filters) {
                $query->whereDate('start_date', '<=', $travelDate->toDateString())
                    ->whereDate('end_date', '>=', $travelDate->toDateString());

                if (!empty($filters['location_id'])) {
                    $query->where(function ($locationQuery) use ($filters) {
                        $locationQuery->whereNull('location_id')
                            ->orWhere('location_id', (int) $filters['location_id']);
                    });
                }
            })
            ->when(!empty($filters['rate_year_id']), fn ($query) => $query->where('rate_year_id', (int) $filters['rate_year_id']))
            ->when(!empty($filters['room_type_id']), fn ($query) => $query->where('room_type_id', (int) $filters['room_type_id']))
            ->when(!empty($filters['meal_plan_id']), fn ($query) => $query->where('meal_plan_id', (int) $filters['meal_plan_id']))
            ->orderBy('rate_year_id')
            ->orderBy('season_id')
            ->get();
    }

    public function extraFees(Hotel $hotel, CarbonInterface $travelDate): Collection
    {
        return $hotel->extraFees()
            ->where(function ($query) use ($travelDate) {
                $query->whereNull('valid_from')
                    ->orWhereDate('valid_from', '<=', $travelDate->toDateString());
            })
            ->where(function ($query) use ($travelDate) {
                $query->whereNull('valid_to')
                    ->orWhereDate('valid_to', '>=', $travelDate->toDateString());
            })
            ->orderBy('name')
            ->get();
    }

    public function holidaySupplements(Hotel $hotel, CarbonInterface $travelDate, array $filters = []): Collection
    {
        return $hotel->holidaySupplements()
            ->with('roomType')
            ->whereDate('start_date', '<=', $travelDate->toDateString())
            ->whereDate('end_date', '>=', $travelDate->toDateString())
            ->when(!empty($filters['room_type_id']), function ($query) use ($filters) {
                $query->where(function ($roomQuery) use ($filters) {
                    $roomQuery->whereNull('room_type_id')
                        ->orWhere('room_type_id', (int) $filters['room_type_id']);
                });
            })
            ->orderBy('holiday_name')
            ->get();
    }

    public function activities(Hotel $hotel): Collection
    {
        return $hotel->accommodationActivities()->orderBy('name')->get();
    }

    public function childPolicies(Hotel $hotel, CarbonInterface $travelDate, array $filters = []): Collection
    {
        return $hotel->childPolicies()
            ->with(['roomType', 'mealPlan', 'season'])
            ->when(!empty($filters['room_type_id']), function ($query) use ($filters) {
                $query->where(function ($roomQuery) use ($filters) {
                    $roomQuery->whereNull('room_type_id')
                        ->orWhere('room_type_id', (int) $filters['room_type_id']);
                });
            })
            ->when(!empty($filters['meal_plan_id']), function ($query) use ($filters) {
                $query->where(function ($mealQuery) use ($filters) {
                    $mealQuery->whereNull('meal_plan_id')
                        ->orWhere('meal_plan_id', (int) $filters['meal_plan_id']);
                });
            })
            ->where(function ($query) use ($travelDate) {
                $query->whereNull('season_id')
                    ->orWhereHas('season', function ($seasonQuery) use ($travelDate) {
                        $seasonQuery->whereDate('start_date', '<=', $travelDate->toDateString())
                            ->whereDate('end_date', '>=', $travelDate->toDateString());
                    });
            })
            ->orderBy('min_age')
            ->get();
    }
}
