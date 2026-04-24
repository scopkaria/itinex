<?php

namespace App\Services\Pricing;

use App\Models\Itinerary\Itinerary;
use App\Models\Itinerary\ItineraryPricingOverride;

class PartnerPricingOverrideService
{
    public function activeOverride(Itinerary $itinerary, ?string $partnerType, ?string $partnerKey): ?ItineraryPricingOverride
    {
        if (!$partnerType || !$partnerKey) {
            return null;
        }

        return ItineraryPricingOverride::query()
            ->where('itinerary_id', $itinerary->id)
            ->where('company_id', $itinerary->company_id)
            ->where('partner_type', $partnerType)
            ->where('partner_key', $partnerKey)
            ->where('is_active', true)
            ->first();
    }

    public function apply(float $baseTotal, ?ItineraryPricingOverride $override): array
    {
        if (!$override) {
            return [
                'base_total' => round($baseTotal, 2),
                'override_amount' => 0.0,
                'final_total' => round($baseTotal, 2),
                'override' => null,
            ];
        }

        $amount = $override->override_mode === 'percent'
            ? round($baseTotal * ((float) $override->override_value / 100), 2)
            : round((float) $override->override_value, 2);

        $finalTotal = round(max(0, $baseTotal + $amount), 2);

        return [
            'base_total' => round($baseTotal, 2),
            'override_amount' => $amount,
            'final_total' => $finalTotal,
            'override' => [
                'id' => $override->id,
                'partner_type' => $override->partner_type,
                'partner_key' => $override->partner_key,
                'override_mode' => $override->override_mode,
                'override_value' => (float) $override->override_value,
            ],
        ];
    }
}
