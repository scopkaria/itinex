<?php

namespace App\Services\Pricing;

class PricingBreakdown
{
    public function __construct(
        public readonly float $baseRate,
        public readonly float $seasonalAdjustment,
        public readonly float $configurationAdjustments,
        public readonly float $childAdjustments,
        public readonly float $supplements,
        public readonly float $moduleTotals,
        public readonly float $subtotal,
        public readonly float $discountAmount,
        public readonly float $markupAmount,
        public readonly float $vatAmount,
        public readonly float $finalTotal
    ) {
    }

    public function toArray(): array
    {
        return [
            'base_rate' => $this->baseRate,
            'seasonal_adjustment' => $this->seasonalAdjustment,
            'configuration_adjustments' => $this->configurationAdjustments,
            'child_adjustments' => $this->childAdjustments,
            'supplements' => $this->supplements,
            'module_totals' => $this->moduleTotals,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discountAmount,
            'markup_amount' => $this->markupAmount,
            'vat_amount' => $this->vatAmount,
            'final_total' => $this->finalTotal,
        ];
    }
}
