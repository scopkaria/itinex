<?php

namespace App\Services\Pricing\Policies;

use App\Models\MasterData\FlightProvider;

class FlightPolicyAdapter implements PricingPolicyAdapter
{
    public function module(): string
    {
        return 'flight';
    }

    public function resolve(int $providerId): array
    {
        $provider = FlightProvider::query()->with(['paymentPolicies', 'cancellationPolicies.season'])->findOrFail($providerId);

        return [
            'module' => $this->module(),
            'provider_id' => $provider->id,
            'provider_name' => $provider->name,
            'payment_policies' => $provider->paymentPolicies->map(fn ($policy) => [
                'id' => $policy->id,
                'days_before_arrival' => $policy->days_before_arrival,
                'percentage_due' => (float) $policy->percentage_due,
            ])->values(),
            'cancellation_policies' => $provider->cancellationPolicies->map(fn ($policy) => [
                'id' => $policy->id,
                'days_before_travel' => $policy->days_before_travel,
                'penalty_percentage' => (float) $policy->penalty_percentage,
                'season' => $policy->season?->name,
            ])->values(),
        ];
    }
}
