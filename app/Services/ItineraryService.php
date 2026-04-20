<?php

namespace App\Services;

use App\Models\Itinerary\Itinerary;
use App\Models\Itinerary\ItineraryItem;
use App\Models\MasterData\Activity;
use App\Models\MasterData\Extra;
use App\Models\MasterData\DestinationFee;
use App\Models\MasterData\Flight;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\Vehicle;

class ItineraryService
{
    /**
     * Calculate cost for a single item based on its type and the itinerary context.
     */
    public function calculateItemCost(ItineraryItem $item, Itinerary $itinerary): float
    {
        $people = $itinerary->number_of_people;
        $quantity = $item->quantity;

        return match ($item->type) {
            'hotel' => $this->calculateHotelCost($item->reference_id, $people, $quantity),
            'transport' => $this->calculateTransportCost($item->reference_id, $people, $quantity),
            'park_fee' => $this->calculateParkFeeCost($item->reference_id, $people, $quantity),
            'activity' => $this->calculateActivityCost($item->reference_id, $people, $quantity),
            'extra' => $this->calculateExtraCost($item->reference_id, $people, $quantity),
            'flight' => $this->calculateFlightCost($item->reference_id, $people, $quantity),
            default => 0,
        };
    }

    /**
     * Hotel: price_per_person × number_of_people × nights (quantity = nights)
     */
    private function calculateHotelCost(int $rateId, int $people, int $nights): float
    {
        $rate = HotelRate::find($rateId);
        if (!$rate) {
            return 0;
        }

        return round((float) $rate->price_per_person * $people * $nights, 2);
    }

    /**
     * Transport (shared vehicle): vehicle_price_per_day × days
     * cost_per_person = total / number_of_people (stored as total for group)
     */
    private function calculateTransportCost(int $vehicleId, int $people, int $days): float
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return 0;
        }

        return round((float) $vehicle->price_per_day * $days, 2);
    }

    /**
     * Park fee: non_resident_adult × number_of_people × days, with optional markup
     */
    private function calculateParkFeeCost(int $feeId, int $people, int $days): float
    {
        $fee = DestinationFee::find($feeId);
        if (!$fee) {
            return 0;
        }

        $base = round((float) $fee->non_resident_adult * $people * $days, 2);
        $markup = (float) $fee->markup;
        if ($markup > 0) {
            $base = round($base * (1 + $markup / 100), 2);
        }
        return $base;
    }

    /**
     * Activity: price_per_person × number_of_people × quantity
     */
    private function calculateActivityCost(int $activityId, int $people, int $quantity): float
    {
        $activity = Activity::find($activityId);
        if (!$activity) {
            return 0;
        }

        return round((float) $activity->price_per_person * $people * $quantity, 2);
    }

    /**
     * Extra: price × quantity (flat pricing, not per-person)
     */
    private function calculateExtraCost(int $extraId, int $people, int $quantity): float
    {
        $extra = Extra::find($extraId);
        if (!$extra) {
            return 0;
        }

        return round((float) $extra->price * $quantity, 2);
    }

    /**
     * Flight: price_per_person × number_of_people × quantity
     */
    private function calculateFlightCost(int $flightId, int $people, int $quantity): float
    {
        $flight = Flight::find($flightId);
        if (!$flight) {
            return 0;
        }

        return round((float) $flight->price_per_person * $people * $quantity, 2);
    }

    /**
     * Sum all item costs across the itinerary.
     */
    public function calculateTotalCost(Itinerary $itinerary): float
    {
        $itinerary->load('days.items');
        $total = 0;

        foreach ($itinerary->days as $day) {
            foreach ($day->items as $item) {
                $total += (float) $item->cost;
            }
        }

        return round($total, 2);
    }

    /**
     * Sum all item prices (selling price) across the itinerary.
     */
    public function calculateTotalPrice(Itinerary $itinerary): float
    {
        $itinerary->load('days.items');
        $total = 0;

        foreach ($itinerary->days as $day) {
            foreach ($day->items as $item) {
                $total += (float) $item->price;
            }
        }

        return round($total, 2);
    }

    /**
     * Profit = total_price - total_cost
     */
    public function calculateProfit(Itinerary $itinerary): float
    {
        return round(
            $this->calculateTotalPrice($itinerary) - $this->calculateTotalCost($itinerary),
            2
        );
    }

    /**
     * Recalculate and persist all itinerary totals including margin.
     */
    public function recalculate(Itinerary $itinerary): Itinerary
    {
        $totalCost = $this->calculateTotalCost($itinerary);
        $totalPrice = $this->calculateTotalPrice($itinerary);
        $totalDays = $itinerary->days()->count();

        $markup = (float) $itinerary->markup_percentage;

        if ($markup > 0) {
            // Markup overrides item-level selling prices
            $totalPrice = round($totalCost + ($totalCost * $markup / 100), 2);
        }

        $profit = round($totalPrice - $totalCost, 2);
        $margin = $totalPrice > 0 ? round(($profit / $totalPrice) * 100, 2) : 0;

        $itinerary->update([
            'total_days' => $totalDays,
            'total_cost' => $totalCost,
            'total_price' => $totalPrice,
            'profit' => $profit,
            'margin_percentage' => $margin,
        ]);

        return $itinerary->fresh(['days.items']);
    }

    /**
     * Build the clean summary response.
     */
    public function summary(Itinerary $itinerary): array
    {
        return [
            'total_cost' => (float) $itinerary->total_cost,
            'total_price' => (float) $itinerary->total_price,
            'profit' => (float) $itinerary->profit,
            'markup_percentage' => (float) $itinerary->markup_percentage,
            'margin_percentage' => (float) $itinerary->margin_percentage,
            'profit_status' => $itinerary->profitStatus(),
        ];
    }
}
