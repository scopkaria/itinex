<?php

namespace App\Services\Pricing\Policies;

interface PricingPolicyAdapter
{
    public function module(): string;

    public function resolve(int $providerId): array;
}
