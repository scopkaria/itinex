<?php

namespace App\Services;

use App\Models\Itinerary\Itinerary;

class ProfitService
{
    /**
     * Apply a markup percentage to a cost.
     * Returns the markup amount.
     */
    public function applyMarkup(float $cost, float $percentage): float
    {
        return round($cost * $percentage / 100, 2);
    }

    /**
     * Calculate selling price from cost + markup.
     * selling_price = total_cost + (total_cost × markup_percentage / 100)
     */
    public function calculateSellingPrice(float $totalCost, float $markupPercentage): float
    {
        return round($totalCost + $this->applyMarkup($totalCost, $markupPercentage), 2);
    }

    /**
     * Calculate profit.
     * profit = selling_price - total_cost
     */
    public function calculateProfit(float $sellingPrice, float $totalCost): float
    {
        return round($sellingPrice - $totalCost, 2);
    }

    /**
     * Calculate margin percentage.
     * margin = (profit / selling_price) × 100
     */
    public function calculateMargin(float $profit, float $sellingPrice): float
    {
        if ($sellingPrice == 0) {
            return 0;
        }

        return round(($profit / $sellingPrice) * 100, 2);
    }

    /**
     * Get profit status based on margin.
     *
     * "profit" → margin > 20%
     * "low"    → margin 5%–20% (or 0%-5%)
     * "loss"   → margin < 0%
     */
    public function profitStatus(float $marginPercentage): string
    {
        if ($marginPercentage > 20) {
            return 'profit';
        }

        if ($marginPercentage < 0) {
            return 'loss';
        }

        return 'low';
    }

    /**
     * Apply markup to an itinerary, recalculate selling price / profit / margin,
     * and persist.
     */
    public function applyMarkupToItinerary(Itinerary $itinerary, float $markupPercentage): array
    {
        $totalCost = (float) $itinerary->total_cost;
        $sellingPrice = $this->calculateSellingPrice($totalCost, $markupPercentage);
        $profit = $this->calculateProfit($sellingPrice, $totalCost);
        $margin = $this->calculateMargin($profit, $sellingPrice);

        $itinerary->update([
            'markup_percentage' => $markupPercentage,
            'total_price' => $sellingPrice,
            'profit' => $profit,
            'margin_percentage' => $margin,
        ]);

        return $this->buildResponse($itinerary->fresh());
    }

    /**
     * Calculate P&L for an itinerary (read-only, no persistence).
     */
    public function getProfitAndLoss(Itinerary $itinerary): array
    {
        return $this->buildResponse($itinerary);
    }

    /**
     * Full recalculate: use item-level prices if no markup set, or markup if set.
     */
    public function recalculate(Itinerary $itinerary, float $itemSellingTotal): array
    {
        $totalCost = (float) $itinerary->total_cost;
        $markup = (float) $itinerary->markup_percentage;

        if ($markup > 0) {
            // Markup takes precedence — override item-level prices
            $sellingPrice = $this->calculateSellingPrice($totalCost, $markup);
        } else {
            // Use sum of item-level selling prices
            $sellingPrice = $itemSellingTotal;
        }

        $profit = $this->calculateProfit($sellingPrice, $totalCost);
        $margin = $this->calculateMargin($profit, $sellingPrice);

        $itinerary->update([
            'total_price' => $sellingPrice,
            'profit' => $profit,
            'margin_percentage' => $margin,
        ]);

        return $this->buildResponse($itinerary->fresh());
    }

    /**
     * Build the standard P&L response.
     */
    private function buildResponse(Itinerary $itinerary): array
    {
        $totalCost = (float) $itinerary->total_cost;
        $sellingPrice = (float) $itinerary->total_price;
        $profit = (float) $itinerary->profit;
        $margin = (float) $itinerary->margin_percentage;
        $people = $itinerary->number_of_people;

        return [
            'itinerary_id' => $itinerary->id,
            'client_name' => $itinerary->client_name,
            'total_cost' => $totalCost,
            'selling_price' => $sellingPrice,
            'markup_percentage' => (float) $itinerary->markup_percentage,
            'profit' => $profit,
            'margin_percentage' => $margin,
            'status' => $this->profitStatus($margin),
            'per_person_cost' => $people > 0 ? round($totalCost / $people, 2) : 0,
            'per_person_price' => $people > 0 ? round($sellingPrice / $people, 2) : 0,
        ];
    }
}
