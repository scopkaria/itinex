<?php

namespace App\Services;

use App\Models\Itinerary\Itinerary;
use App\Models\Itinerary\ItineraryItem;
use App\Models\MasterData\Activity;
use App\Models\MasterData\Extra;
use App\Models\MasterData\ParkFee;
use App\Models\MasterData\Flight;
use App\Models\MasterData\ScheduledFlight;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\TransportTransferRate;
use App\Models\MasterData\Vehicle;
use App\Services\Pricing\PartnerPricingOverrideService;
use App\Services\Pricing\PricingEngineService;
use App\Services\Pricing\PricingInput;

class ItineraryService
{
    public function __construct(
        private readonly PricingEngineService $pricingEngine,
        private readonly PartnerPricingOverrideService $partnerOverrideService,
    ) {
    }

    /**
     * Calculate cost for a single item based on its type and the itinerary context.
     */
    public function calculateItemCost(ItineraryItem $item, Itinerary $itinerary): float
    {
        $manualCost = data_get($item->meta, 'manual_cost');
        if ($manualCost !== null) {
            return round((float) $manualCost, 2);
        }

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

        return $this->computeTotal((float) $rate->price_per_person * $people * $nights);
    }

    /**
     * Transport (shared vehicle): vehicle_price_per_day × days
     * cost_per_person = total / number_of_people (stored as total for group)
     */
    private function calculateTransportCost(int $vehicleId, int $people, int $days): float
    {
        // Transfer rates are stored as transport items with an explicit source.
        $transferRate = TransportTransferRate::find($vehicleId);
        if ($transferRate) {
            return $this->computeTotal((float) ($transferRate->sell_price ?? $transferRate->buy_price ?? 0) * $days);
        }

        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return 0;
        }

        return $this->computeTotal((float) $vehicle->price_per_day * $days);
    }

    /**
     * Park fee: non_resident_adult × number_of_people × days, with optional markup
     */
    private function calculateParkFeeCost(int $feeId, int $people, int $days): float
    {
        $fee = ParkFee::find($feeId);
        if (!$fee) {
            return 0;
        }

        $base = (float) $fee->non_resident_adult * $people * $days;
        $markup = (float) $fee->markup;

        return $this->computeTotal($base, $markup);
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

        return $this->computeTotal((float) $activity->price_per_person * $people * $quantity);
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

        return $this->computeTotal((float) $extra->price * $quantity);
    }

    /**
     * Flight: price_per_person × number_of_people × quantity
     */
    private function calculateFlightCost(int $flightId, int $people, int $quantity): float
    {
        $scheduled = ScheduledFlight::find($flightId);
        if ($scheduled) {
            $adultPrice = (float) $scheduled->base_adult_price;
            $childPrice = (float) $scheduled->base_child_price;
            $base = (($adultPrice + $childPrice) / 2) * $people * $quantity;

            return $this->computeTotal($base);
        }

        $flight = Flight::find($flightId);
        if (!$flight) {
            return 0;
        }

        return $this->computeTotal((float) $flight->price_per_person * $people * $quantity);
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

        // Always derive totals from the pricing engine for consistency.
        $totalPrice = $this->computeTotal($totalCost, $markup);

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
    public function summary(Itinerary $itinerary, ?string $partnerType = null, ?string $partnerKey = null): array
    {
        $override = $this->partnerOverrideService->activeOverride($itinerary, $partnerType, $partnerKey);
        $overrideTotals = $this->partnerOverrideService->apply((float) $itinerary->total_price, $override);

        return [
            'total_cost' => (float) $itinerary->total_cost,
            'base_total_price' => $overrideTotals['base_total'],
            'total_price' => $overrideTotals['final_total'],
            'override_amount' => $overrideTotals['override_amount'],
            'override' => $overrideTotals['override'],
            'profit' => round($overrideTotals['final_total'] - (float) $itinerary->total_cost, 2),
            'markup_percentage' => (float) $itinerary->markup_percentage,
            'margin_percentage' => $overrideTotals['final_total'] > 0
                ? round((($overrideTotals['final_total'] - (float) $itinerary->total_cost) / $overrideTotals['final_total']) * 100, 2)
                : 0,
            'profit_status' => $overrideTotals['final_total'] > (float) $itinerary->total_cost
                ? 'profit'
                : ($overrideTotals['final_total'] < (float) $itinerary->total_cost ? 'loss' : 'low'),
        ];
    }

    private function computeTotal(float $baseRate, float $markupPercent = 0): float
    {
        $breakdown = $this->pricingEngine->compute(new PricingInput(
            baseRate: $baseRate,
            markupPercent: $markupPercent,
        ));

        return round($breakdown->finalTotal, 2);
    }
}
