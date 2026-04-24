<?php

namespace App\Services\Pricing\Policies;

use InvalidArgumentException;

class PricingPolicyAdapterRegistry
{
    /** @var array<string, PricingPolicyAdapter> */
    private array $adapters;

    public function __construct()
    {
        $instances = [
            new AccommodationPolicyAdapter(),
            new FlightPolicyAdapter(),
            new TransportPolicyAdapter(),
        ];

        $this->adapters = [];

        foreach ($instances as $adapter) {
            $this->adapters[$adapter->module()] = $adapter;
        }
    }

    public function resolve(string $module, int $providerId): array
    {
        if (!isset($this->adapters[$module])) {
            throw new InvalidArgumentException('Unsupported module adapter: ' . $module);
        }

        return $this->adapters[$module]->resolve($providerId);
    }
}
