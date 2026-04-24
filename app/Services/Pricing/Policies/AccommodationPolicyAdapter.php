<?php

namespace App\Services\Pricing\Policies;

use App\Models\MasterData\Hotel;

class AccommodationPolicyAdapter implements PricingPolicyAdapter
{
    public function module(): string
    {
        return 'accommodation';
    }

    public function resolve(int $providerId): array
    {
        $hotel = Hotel::query()->with(['paymentPolicies', 'cancellationPolicies'])->findOrFail($providerId);

        return [
            'module' => $this->module(),
            'provider_id' => $hotel->id,
            'provider_name' => $hotel->name,
            'payment_policies' => $hotel->paymentPolicies->map(fn ($policy) => [
                'id' => $policy->id,
                'title' => $policy->title,
                'days_before' => $policy->days_before,
                'percentage' => $policy->percentage,
                'content' => $policy->content,
            ])->values(),
            'cancellation_policies' => $hotel->cancellationPolicies->map(fn ($policy) => [
                'id' => $policy->id,
                'days_before' => $policy->days_before,
                'penalty_percentage' => $policy->penalty_percentage,
                'description' => $policy->description,
            ])->values(),
        ];
    }
}
