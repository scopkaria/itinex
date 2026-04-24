<?php

namespace App\Services\Pricing;

class PricingInput
{
    public function __construct(
        public readonly float $baseRate,
        public readonly float $seasonalPercent = 0.0,
        public readonly float $seasonalFixed = 0.0,
        public readonly float $mealPlanAdjustment = 0.0,
        public readonly float $roomTypeAdjustment = 0.0,
        public readonly float $childDiscountPercent = 0.0,
        public readonly float $childDiscountFixed = 0.0,
        public readonly float $singleSupplement = 0.0,
        public readonly float $tripleReduction = 0.0,
        public readonly float $transportTotal = 0.0,
        public readonly float $activitiesTotal = 0.0,
        public readonly float $flightsTotal = 0.0,
        public readonly float $discountPercent = 0.0,
        public readonly float $discountFixed = 0.0,
        public readonly float $markupPercent = 0.0,
        public readonly float $markupFixed = 0.0,
        public readonly float $vatPercent = 0.0,
        public readonly string $vatType = 'exclusive'
    ) {
    }
}
