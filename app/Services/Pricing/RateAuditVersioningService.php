<?php

namespace App\Services\Pricing;

use App\Models\MasterData\AccommodationRateVersion;
use App\Models\MasterData\FlightRateVersion;
use App\Models\MasterData\TransportRateVersion;
use App\Models\PricingAuditLog;

class RateAuditVersioningService
{
    public function record(
        string $module,
        int $companyId,
        ?int $providerId,
        ?string $providerType,
        string $entityType,
        ?int $entityId,
        string $action,
        ?array $beforeState,
        ?array $afterState,
        ?int $changedBy,
        string $source = 'web'
    ): void {
        PricingAuditLog::create([
            'company_id' => $companyId,
            'module' => $module,
            'provider_id' => $providerId,
            'provider_type' => $providerType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'changed_by' => $changedBy,
            'source' => $source,
        ]);

        $this->recordVersionRow(
            module: $module,
            providerId: $providerId,
            entityType: $entityType,
            entityId: $entityId,
            changeType: $action,
            oldValue: $this->extractRateValue($beforeState),
            newValue: $this->extractRateValue($afterState),
            changedBy: $changedBy
        );
    }

    private function recordVersionRow(
        string $module,
        ?int $providerId,
        string $entityType,
        ?int $entityId,
        string $changeType,
        ?float $oldValue,
        ?float $newValue,
        ?int $changedBy
    ): void {
        if ($providerId === null || $entityId === null) {
            return;
        }

        if ($module === 'flight') {
            FlightRateVersion::create([
                'flight_provider_id' => $providerId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'change_type' => $changeType,
                'changed_by' => $changedBy,
            ]);

            return;
        }

        if ($module === 'transport') {
            TransportRateVersion::create([
                'transport_provider_id' => $providerId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'change_type' => $changeType,
                'changed_by' => $changedBy,
            ]);

            return;
        }

        if ($module === 'accommodation') {
            AccommodationRateVersion::create([
                'hotel_id' => $providerId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'change_type' => $changeType,
                'changed_by' => $changedBy,
            ]);
        }
    }

    private function extractRateValue(?array $state): ?float
    {
        if (!$state) {
            return null;
        }

        foreach (['sell_price', 'adult_rate', 'rate', 'base_adult_price', 'total_charter_price', 'buy_price', 'derived_rate'] as $key) {
            if (array_key_exists($key, $state) && $state[$key] !== null && $state[$key] !== '') {
                return round((float) $state[$key], 2);
            }
        }

        return null;
    }
}
