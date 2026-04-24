<?php

namespace App\Services\Pricing;

class PricingEngineService
{
    public function compute(PricingInput $input): PricingBreakdown
    {
        $seasonalAdjustment = round(($input->baseRate * ($input->seasonalPercent / 100)) + $input->seasonalFixed, 2);
        $configurationAdjustments = round($input->mealPlanAdjustment + $input->roomTypeAdjustment, 2);

        $childPercentAdjustment = round(-1 * ($input->baseRate * ($input->childDiscountPercent / 100)), 2);
        $childFixedAdjustment = round(-1 * $input->childDiscountFixed, 2);
        $childAdjustments = round($childPercentAdjustment + $childFixedAdjustment, 2);

        $supplements = round($input->singleSupplement - $input->tripleReduction, 2);
        $moduleTotals = round($input->transportTotal + $input->activitiesTotal + $input->flightsTotal, 2);

        $subtotal = round(
            $input->baseRate +
            $seasonalAdjustment +
            $configurationAdjustments +
            $childAdjustments +
            $supplements +
            $moduleTotals,
            2
        );

        $discountAmount = round(($subtotal * ($input->discountPercent / 100)) + $input->discountFixed, 2);
        $discountedSubtotal = round(max(0, $subtotal - $discountAmount), 2);

        $markupAmount = round(($discountedSubtotal * ($input->markupPercent / 100)) + $input->markupFixed, 2);
        $preVatTotal = round($discountedSubtotal + $markupAmount, 2);

        $vatAmount = 0.0;
        $finalTotal = $preVatTotal;

        if ($input->vatPercent > 0) {
            if ($input->vatType === 'inclusive') {
                $vatAmount = round($preVatTotal - ($preVatTotal / (1 + ($input->vatPercent / 100))), 2);
            } else {
                $vatAmount = round($preVatTotal * ($input->vatPercent / 100), 2);
                $finalTotal = round($preVatTotal + $vatAmount, 2);
            }
        }

        return new PricingBreakdown(
            baseRate: round($input->baseRate, 2),
            seasonalAdjustment: $seasonalAdjustment,
            configurationAdjustments: $configurationAdjustments,
            childAdjustments: $childAdjustments,
            supplements: $supplements,
            moduleTotals: $moduleTotals,
            subtotal: $subtotal,
            discountAmount: $discountAmount,
            markupAmount: $markupAmount,
            vatAmount: $vatAmount,
            finalTotal: round($finalTotal, 2)
        );
    }
}
