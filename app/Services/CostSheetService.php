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

class CostSheetService
{
    /**
     * Generate a full cost sheet for an itinerary.
     */
    public function generate(Itinerary $itinerary): array
    {
        $itinerary->load('days.items');
        $people = $itinerary->number_of_people;

        $breakdown = [
            'accommodation' => [],
            'park_fees' => [],
            'transport' => [],
            'flights' => [],
            'extras' => [],
        ];

        foreach ($itinerary->days as $day) {
            foreach ($day->items as $item) {
                $entry = $this->buildLineItem($item, $people, $day->day_number);
                if ($entry) {
                    $category = $this->categoryForType($item->type);
                    $breakdown[$category][] = $entry;
                }
            }
        }

        $totals = $this->calculateCategoryTotals($breakdown, $people);

        return [
            'itinerary_id' => $itinerary->id,
            'client_name' => $itinerary->client_name,
            'number_of_people' => $people,
            'total_days' => $itinerary->total_days,
            'breakdown' => $breakdown,
            'totals' => $totals,
        ];
    }

    /**
     * Get only the summary totals (no line-item breakdown).
     */
    public function summary(Itinerary $itinerary): array
    {
        $sheet = $this->generate($itinerary);
        return $sheet['totals'];
    }

    /**
     * Get per-category totals.
     */
    public function categoryTotals(Itinerary $itinerary): array
    {
        $sheet = $this->generate($itinerary);

        return [
            'accommodation_total' => $sheet['totals']['accommodation_total'],
            'park_total' => $sheet['totals']['park_total'],
            'transport_total' => $sheet['totals']['transport_total'],
            'flight_total' => $sheet['totals']['flight_total'],
            'extras_total' => $sheet['totals']['extras_total'],
            'grand_total' => $sheet['totals']['grand_total'],
            'per_person_cost' => $sheet['totals']['per_person_cost'],
        ];
    }

    // ────────────────────────────────────────────────────────────
    // Line-item builders by type
    // ────────────────────────────────────────────────────────────

    private function buildLineItem(ItineraryItem $item, int $people, int $dayNumber): ?array
    {
        return match ($item->type) {
            'hotel' => $this->buildAccommodationLine($item, $people, $dayNumber),
            'park_fee' => $this->buildParkFeeLine($item, $people, $dayNumber),
            'transport' => $this->buildTransportLine($item, $people, $dayNumber),
            'flight' => $this->buildFlightLine($item, $people, $dayNumber),
            'activity' => $this->buildActivityLine($item, $people, $dayNumber),
            'extra' => $this->buildExtraLine($item, $people, $dayNumber),
            default => null,
        };
    }

    /**
     * Accommodation: price_per_person × number_of_people × nights
     */
    private function buildAccommodationLine(ItineraryItem $item, int $people, int $dayNumber): ?array
    {
        $rate = HotelRate::with(['hotel', 'roomType', 'mealPlan'])->find($item->reference_id);
        if (!$rate) {
            return null;
        }

        $nights = $item->quantity;
        $unitCost = (float) $rate->price_per_person;
        $total = round($unitCost * $people * $nights, 2);

        return [
            'day' => $dayNumber,
            'item_id' => $item->id,
            'hotel' => $rate->hotel?->name,
            'room_type' => $rate->roomType?->type,
            'meal_plan' => $rate->mealPlan?->name,
            'season' => $rate->season,
            'price_per_person' => $unitCost,
            'nights' => $nights,
            'people' => $people,
            'total' => $total,
            'selling_price' => (float) $item->price,
        ];
    }

    /**
     * Park fees: price_per_person × number_of_people × days
     */
    private function buildParkFeeLine(ItineraryItem $item, int $people, int $dayNumber): ?array
    {
        $fee = DestinationFee::with('destination')->find($item->reference_id);
        if (!$fee) {
            return null;
        }

        $days = $item->quantity;
        $unitCost = (float) $fee->nr_adult;
        $markup = (float) $fee->markup;
        $costTotal = round($unitCost * $people * $days, 2);
        $sellingUnit = $markup > 0 ? round($unitCost * (1 + $markup / 100), 2) : $unitCost;
        $sellingTotal = round($sellingUnit * $people * $days, 2);

        return [
            'day' => $dayNumber,
            'item_id' => $item->id,
            'park_name' => $fee->destination?->name ?? 'Unknown',
            'resident_type' => 'Non-Resident',
            'season' => $fee->season_name,
            'price_per_person' => $unitCost,
            'markup_pct' => $markup,
            'vat_type' => $fee->vat_type,
            'days' => $days,
            'people' => $people,
            'total' => $costTotal,
            'selling_price' => (float) ($item->price ?: $sellingTotal),
        ];
    }

    /**
     * Transport: price_per_day × days (shared vehicle)
     * per_person = total / number_of_people
     */
    private function buildTransportLine(ItineraryItem $item, int $people, int $dayNumber): ?array
    {
        $vehicle = Vehicle::find($item->reference_id);
        if (!$vehicle) {
            return null;
        }

        $days = $item->quantity;
        $pricePerDay = (float) $vehicle->price_per_day;
        $total = round($pricePerDay * $days, 2);
        $perPerson = $people > 0 ? round($total / $people, 2) : 0;

        return [
            'day' => $dayNumber,
            'item_id' => $item->id,
            'vehicle' => $vehicle->name,
            'capacity' => $vehicle->capacity,
            'price_per_day' => $pricePerDay,
            'days' => $days,
            'total' => $total,
            'per_person' => $perPerson,
            'selling_price' => (float) $item->price,
        ];
    }

    /**
     * Flight: price_per_person × number_of_people
     */
    private function buildFlightLine(ItineraryItem $item, int $people, int $dayNumber): ?array
    {
        $flight = Flight::find($item->reference_id);
        if (!$flight) {
            return null;
        }

        $quantity = $item->quantity;
        $unitCost = (float) $flight->price_per_person;
        $total = round($unitCost * $people * $quantity, 2);

        return [
            'day' => $dayNumber,
            'item_id' => $item->id,
            'flight' => $flight->name,
            'origin' => $flight->origin,
            'destination' => $flight->destination,
            'price_per_person' => $unitCost,
            'quantity' => $quantity,
            'people' => $people,
            'total' => $total,
            'selling_price' => (float) $item->price,
        ];
    }

    /**
     * Activity: price_per_person × number_of_people × quantity
     * (activities go into extras category in the cost sheet)
     */
    private function buildActivityLine(ItineraryItem $item, int $people, int $dayNumber): ?array
    {
        $activity = Activity::find($item->reference_id);
        if (!$activity) {
            return null;
        }

        $quantity = $item->quantity;
        $unitCost = (float) $activity->price_per_person;
        $total = round($unitCost * $people * $quantity, 2);

        return [
            'day' => $dayNumber,
            'item_id' => $item->id,
            'name' => $activity->name,
            'type' => 'activity',
            'price_per_person' => $unitCost,
            'quantity' => $quantity,
            'people' => $people,
            'total' => $total,
            'selling_price' => (float) $item->price,
        ];
    }

    /**
     * Extra: price × quantity (flat)
     */
    private function buildExtraLine(ItineraryItem $item, int $people, int $dayNumber): ?array
    {
        $extra = Extra::find($item->reference_id);
        if (!$extra) {
            return null;
        }

        $quantity = $item->quantity;
        $unitPrice = (float) $extra->price;
        $total = round($unitPrice * $quantity, 2);

        return [
            'day' => $dayNumber,
            'item_id' => $item->id,
            'name' => $extra->name,
            'type' => 'extra',
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total' => $total,
            'selling_price' => (float) $item->price,
        ];
    }

    // ────────────────────────────────────────────────────────────
    // Totals calculation
    // ────────────────────────────────────────────────────────────

    private function calculateCategoryTotals(array $breakdown, int $people): array
    {
        $accommodationTotal = $this->sumCategory($breakdown['accommodation']);
        $parkTotal = $this->sumCategory($breakdown['park_fees']);
        $transportTotal = $this->sumCategory($breakdown['transport']);
        $flightTotal = $this->sumCategory($breakdown['flights']);
        $extrasTotal = $this->sumCategory($breakdown['extras']);

        $grandTotal = round(
            $accommodationTotal + $parkTotal + $transportTotal + $flightTotal + $extrasTotal,
            2
        );

        $sellingTotal = $this->sumSellingPrice($breakdown);
        $profit = round($sellingTotal - $grandTotal, 2);

        $perPersonCost = $people > 0 ? round($grandTotal / $people, 2) : 0;
        $margin = $sellingTotal > 0 ? round(($profit / $sellingTotal) * 100, 2) : 0;

        return [
            'accommodation_total' => $accommodationTotal,
            'park_total' => $parkTotal,
            'transport_total' => $transportTotal,
            'flight_total' => $flightTotal,
            'extras_total' => $extrasTotal,
            'grand_total' => $grandTotal,
            'selling_total' => $sellingTotal,
            'profit' => $profit,
            'margin_percentage' => $margin,
            'profit_status' => $margin > 20 ? 'profit' : ($margin < 0 ? 'loss' : 'low'),
            'per_person_cost' => $perPersonCost,
        ];
    }

    private function sumCategory(array $items): float
    {
        return round(array_sum(array_column($items, 'total')), 2);
    }

    private function sumSellingPrice(array $breakdown): float
    {
        $total = 0;
        foreach ($breakdown as $items) {
            $total += array_sum(array_column($items, 'selling_price'));
        }
        return round($total, 2);
    }

    /**
     * Map item type to cost sheet category.
     */
    private function categoryForType(string $type): string
    {
        return match ($type) {
            'hotel' => 'accommodation',
            'park_fee' => 'park_fees',
            'transport' => 'transport',
            'flight' => 'flights',
            'activity', 'extra' => 'extras',
        };
    }
}
