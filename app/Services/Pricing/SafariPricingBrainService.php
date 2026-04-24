<?php

namespace App\Services\Pricing;

use App\Models\Itinerary\Itinerary;
use App\Models\MasterData\Activity;
use App\Models\MasterData\ParkFee;
use App\Models\MasterData\Extra;
use App\Models\MasterData\FlightChildPricing;
use App\Models\MasterData\FlightRateType;
use App\Models\MasterData\FlightRoute;
use App\Models\MasterData\FlightSeason;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\Package;
use App\Models\MasterData\ScheduledFlight;
use App\Models\MasterData\TransportRate;
use App\Models\MasterData\TransportSeason;
use App\Models\MasterData\TransportTransferRate;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\Cache;

class SafariPricingBrainService
{
    public function quote(
        Itinerary $itinerary,
        string $serviceType,
        array $payload,
        array $globals = [],
        array $pricingRules = [],
        array $existingServices = []
    ): array {
        $globals = $this->normalizeGlobals($itinerary, $globals);
        $pricingRules = $this->normalizePricingRules($pricingRules, $globals);

        $this->validatePaxConsistency($globals);

        $service = match ($serviceType) {
            'accommodation' => $this->quoteAccommodation($itinerary, $payload, $globals),
            'flight' => $this->quoteFlight($payload, $globals),
            'transfer' => $this->quoteTransfer($payload),
            'transport' => $this->quoteTransportPerDay($payload),
            'park_fee' => $this->quoteParkFee($payload, $globals),
            'package' => $this->quotePackage($itinerary, $payload, $globals),
            'extra' => $this->quoteExtra($payload, $globals),
            default => throw new DomainException('Unsupported service type.'),
        };

        $serviceTotals = $this->applyServiceAdjustments(
            $service['type'],
            (float) $service['base_total'],
            $pricingRules,
            $globals['pax_total']
        );

        $service['service_total'] = $serviceTotals['final_total'];
        $service['markup_amount'] = $serviceTotals['markup_amount'];
        $service['discount_amount'] = $serviceTotals['discount_amount'];

        $allServices = array_merge($existingServices, [[
            'type' => $service['type'],
            'base_total' => $service['base_total'],
        ]]);

        $combined = $this->combineAllServices($allServices, $pricingRules, $globals['pax_total']);

        return [
            'service' => $service,
            'combined' => $combined,
        ];
    }

    public function combineAllServices(array $services, array $pricingRules, int $paxTotal): array
    {
        $subtotal = 0.0;
        $markup = 0.0;

        foreach ($services as $service) {
            $base = max(0, (float) ($service['base_total'] ?? 0));
            $subtotal += $base;

            if ($this->isCovered((string) ($service['type'] ?? ''), $pricingRules['markup_covers'])) {
                if ($pricingRules['markup_type'] === 'fixed') {
                    $markup += (float) $pricingRules['markup_value'] * max(1, $paxTotal);
                } else {
                    $markup += $base * ((float) $pricingRules['markup_value'] / 100);
                }
            }
        }

        $subtotal = round($subtotal, 2);
        $markup = round($markup, 2);

        $discount = 0.0;
        if ($pricingRules['discount_type'] === 'fixed') {
            $discount = (float) $pricingRules['discount_value'];
        } elseif ($pricingRules['discount_type'] === 'percent') {
            $discount = ($subtotal * ((float) $pricingRules['discount_value'] / 100));
        }

        $discount = round($discount, 2);

        $taxableAmount = round(max(0, $subtotal + $markup - $discount), 2);

        $vat = 0.0;
        if ($pricingRules['vat_enabled']) {
            $vat = round($taxableAmount * ((float) $pricingRules['vat_percent'] / 100), 2);
        }

        $grandTotal = round($taxableAmount + $vat, 2);

        return [
            'subtotal' => $subtotal,
            'markup' => $markup,
            'discount' => $discount,
            'taxable_amount' => $taxableAmount,
            'vat' => $vat,
            'grand_total' => $grandTotal,
        ];
    }

    private function quoteAccommodation(Itinerary $itinerary, array $payload, array $globals): array
    {
        $hotelId = (int) ($payload['hotel_id'] ?? 0);
        $roomTypeId = isset($payload['room_type_id']) ? (int) $payload['room_type_id'] : null;
        $arrivalDate = (string) ($payload['arrival_date'] ?? '');
        $nights = max(1, (int) ($payload['nights'] ?? 1));
        $season = (string) ($payload['season'] ?? ($globals['season'] ?? ''));

        if (!$hotelId || !$arrivalDate) {
            throw new DomainException('Accommodation requires property and arrival date.');
        }

        $this->validateAccommodationOverlap($itinerary, $arrivalDate, $nights);

        $cacheKey = 'pricing:hotel-rate:' . md5(json_encode([$hotelId, $roomTypeId, $season, $arrivalDate, $payload['rate_type'] ?? null]));
        $rate = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($hotelId, $roomTypeId, $season, $arrivalDate) {
            return HotelRate::query()
                ->where('hotel_id', $hotelId)
                ->when($roomTypeId, fn ($q) => $q->where('room_type_id', $roomTypeId))
                ->when($season !== '', fn ($q) => $q->where('season', $season))
                ->whereDate('start_date', '<=', $arrivalDate)
                ->whereDate('end_date', '>=', $arrivalDate)
                ->orderBy('price_per_person')
                ->first();
        });

        if (!$rate) {
            throw new DomainException('No accommodation rate found.');
        }

        $adults = (int) $globals['adults'];
        $children = (int) $globals['children'];

        $adultRate = (float) ($payload['adult_rate'] ?? $rate->price_per_person);
        $childRate = (float) ($payload['child_rate'] ?? $rate->price_per_person);

        $roomTotal = ($adults * $adultRate) + ($children * $childRate);
        $base = $roomTotal * $nights;

        $singleSupplement = (float) ($payload['single_supplement'] ?? 0);
        $tripleReduction = (float) ($payload['triple_reduction'] ?? 0);

        $base += $singleSupplement;
        $base -= $tripleReduction;

        if ($base < 0) {
            throw new DomainException('Accommodation total cannot be negative.');
        }

        $base = round($base, 2);

        return [
            'type' => 'accommodation',
            'label' => 'Accommodation rate matched',
            'base_total' => $base,
            'breakdown' => [
                'adult_rate' => round($adultRate, 2),
                'child_rate' => round($childRate, 2),
                'adults' => $adults,
                'children' => $children,
                'nights' => $nights,
                'single_supplement' => round($singleSupplement, 2),
                'triple_reduction' => round($tripleReduction, 2),
            ],
            'item' => [
                'type' => 'hotel',
                'reference_id' => $rate->id,
                'reference_source' => 'hotel_rate',
                'quantity' => $nights,
                'meta' => [
                    'manual_cost' => $base,
                    'arrival_date' => $arrivalDate,
                    'nights' => $nights,
                    'pax_total' => $globals['pax_total'],
                ],
            ],
        ];
    }

    private function quoteFlight(array $payload, array $globals): array
    {
        $providerId = (int) ($payload['provider_id'] ?? 0);
        $travelDate = (string) ($payload['date'] ?? '');
        $rateTypeName = strtoupper(trim((string) ($payload['rate_type'] ?? 'STO')));

        $fromId = (int) ($payload['from_destination_id'] ?? 0);
        $toId = (int) ($payload['to_destination_id'] ?? 0);
        $routeId = (int) ($payload['route_id'] ?? 0);

        if (!$providerId || (!$routeId && (!$fromId || !$toId))) {
            throw new DomainException('Flight requires provider and route (or from/to).');
        }

        if (!$routeId) {
            $route = Cache::remember(
                'pricing:flight-route:' . md5(json_encode([$providerId, $fromId, $toId])),
                now()->addMinutes(20),
                fn () => FlightRoute::query()
                    ->where('flight_provider_id', $providerId)
                    ->where('origin_destination_id', $fromId)
                    ->where('arrival_destination_id', $toId)
                    ->first()
            );
            $routeId = (int) ($route?->id ?? 0);
        }

        if (!$routeId) {
            throw new DomainException('ERROR: No rate found');
        }

        $season = Cache::remember(
            'pricing:flight-season:' . md5(json_encode([$providerId, $travelDate, $globals['season'] ?? null])),
            now()->addMinutes(10),
            function () use ($providerId, $travelDate, $globals) {
                $query = FlightSeason::query()->where('flight_provider_id', $providerId);
                if ($travelDate !== '') {
                    $query->whereDate('start_date', '<=', $travelDate)
                        ->whereDate('end_date', '>=', $travelDate);
                }
                if (!empty($globals['season']) && is_numeric($globals['season'])) {
                    $query->whereKey((int) $globals['season']);
                }
                return $query->orderBy('start_date')->first();
            }
        );

        $rateType = Cache::remember(
            'pricing:flight-rate-type:' . md5(json_encode([$providerId, $rateTypeName])),
            now()->addMinutes(20),
            fn () => FlightRateType::query()
                ->where('flight_provider_id', $providerId)
                ->whereRaw('UPPER(name) = ?', [$rateTypeName])
                ->first()
        );

        $flight = Cache::remember(
            'pricing:scheduled-flight:' . md5(json_encode([$providerId, $routeId, $season?->id, $rateType?->id])),
            now()->addMinutes(5),
            function () use ($providerId, $routeId, $season, $rateType) {
                $query = ScheduledFlight::query()
                    ->where('flight_provider_id', $providerId)
                    ->where('flight_route_id', $routeId)
                    ->where('is_active', true);

                if ($season) {
                    $query->where('flight_season_id', $season->id);
                }

                if ($rateType) {
                    $query->where('flight_rate_type_id', $rateType->id);
                }

                return $query->first();
            }
        );

        if (!$flight) {
            throw new DomainException('ERROR: No rate found');
        }

        $adults = (int) $globals['adults'];
        $children = (int) $globals['children'];
        $guides = max(0, (int) ($payload['guides'] ?? 0));
        $childAges = array_values(array_filter(array_map('intval', (array) ($payload['child_ages'] ?? $globals['child_ages'] ?? [])), fn ($age) => $age >= 0));

        $adultRate = (float) $flight->base_adult_price;
        $childRate = (float) $flight->base_child_price;
        $guideRate = (float) ($payload['guide_rate'] ?? $flight->base_guide_price);

        $childPricingRules = FlightChildPricing::query()
            ->where('flight_provider_id', $providerId)
            ->orderBy('min_age')
            ->get();

        $childTotal = 0.0;
        foreach ($childAges as $age) {
            $rule = $childPricingRules->first(fn ($r) => $age >= (int) $r->min_age && $age <= (int) $r->max_age);
            if (!$rule) {
                $childTotal += $childRate;
                continue;
            }

            if ($rule->pricing_type === 'free') {
                continue;
            }

            if ($rule->pricing_type === 'fixed') {
                $childTotal += (float) $rule->value;
                continue;
            }

            $childTotal += $adultRate * ((float) $rule->value / 100);
        }

        if ($children > count($childAges)) {
            $childTotal += ($children - count($childAges)) * $childRate;
        }

        $base = round(($adults * $adultRate) + $childTotal + ($guides * $guideRate), 2);

        if ($base < 0) {
            throw new DomainException('Flight total cannot be negative.');
        }

        return [
            'type' => 'flight',
            'label' => 'Flight STO rate matched',
            'base_total' => $base,
            'breakdown' => [
                'adults' => $adults,
                'children' => $children,
                'guides' => $guides,
                'adult_rate' => round($adultRate, 2),
                'child_rate' => round($childRate, 2),
                'guide_rate' => round($guideRate, 2),
                'season_id' => $season?->id,
                'rate_type' => $rateTypeName,
            ],
            'item' => [
                'type' => 'flight',
                'reference_id' => $flight->id,
                'reference_source' => 'scheduled_flight',
                'quantity' => 1,
                'meta' => [
                    'manual_cost' => $base,
                    'travel_date' => $travelDate,
                    'season_id' => $season?->id,
                    'rate_type' => $rateTypeName,
                    'adults' => $adults,
                    'children' => $children,
                    'guides' => $guides,
                ],
            ],
        ];
    }

    private function quoteTransfer(array $payload): array
    {
        $providerId = (int) ($payload['provider_id'] ?? 0);
        $routeId = (int) ($payload['route_id'] ?? 0);
        $vehicleTypeId = (int) ($payload['vehicle_type_id'] ?? 0);
        $travelDate = (string) ($payload['date'] ?? '');
        $vehicles = max(1, (int) ($payload['vehicles'] ?? 1));

        $season = TransportSeason::query()
            ->where('transport_provider_id', $providerId)
            ->whereDate('start_date', '<=', $travelDate)
            ->whereDate('end_date', '>=', $travelDate)
            ->first();

        $rate = Cache::remember(
            'pricing:transfer-rate:' . md5(json_encode([$providerId, $routeId, $vehicleTypeId, $season?->id])),
            now()->addMinutes(10),
            function () use ($providerId, $routeId, $vehicleTypeId, $season) {
                return TransportTransferRate::query()
                    ->where('transport_provider_id', $providerId)
                    ->where('transfer_route_id', $routeId)
                    ->where('vehicle_type_id', $vehicleTypeId)
                    ->when($season, fn ($q) => $q->where(function ($sub) use ($season) {
                        $sub->where('transport_season_id', $season->id)
                            ->orWhereNull('transport_season_id');
                    }))
                    ->orderByRaw('transport_season_id IS NULL')
                    ->first();
            }
        );

        if (!$rate) {
            throw new DomainException('No transfer rate found for the selected route/vehicle.');
        }

        $vehicleRate = (float) ($rate->sell_price ?? $rate->buy_price ?? 0);
        $base = round($vehicleRate * $vehicles, 2);

        return [
            'type' => 'transfer',
            'label' => 'Transfer rate matched',
            'base_total' => $base,
            'breakdown' => [
                'vehicle_rate' => round($vehicleRate, 2),
                'vehicles' => $vehicles,
                'buy_price' => (float) ($rate->buy_price ?? 0),
                'sell_price' => (float) ($rate->sell_price ?? 0),
                'profit' => round(((float) ($rate->sell_price ?? 0)) - ((float) ($rate->buy_price ?? 0)), 2),
            ],
            'item' => [
                'type' => 'transport',
                'reference_id' => $rate->id,
                'reference_source' => 'transport_transfer_rate',
                'quantity' => $vehicles,
                'meta' => [
                    'manual_cost' => $base,
                    'travel_date' => $travelDate,
                ],
            ],
        ];
    }

    private function quoteTransportPerDay(array $payload): array
    {
        $providerId = (int) ($payload['provider_id'] ?? 0);
        $vehicleTypeId = (int) ($payload['vehicle_type_id'] ?? 0);
        $days = max(1, (int) ($payload['days'] ?? 1));
        $vehicles = max(1, (int) ($payload['vehicles'] ?? 1));
        $travelDate = (string) ($payload['date'] ?? now()->toDateString());

        $rate = Cache::remember(
            'pricing:transport-day-rate:' . md5(json_encode([$providerId, $vehicleTypeId, $travelDate])),
            now()->addMinutes(10),
            fn () => TransportRate::query()
                ->where('transport_provider_id', $providerId)
                ->where('vehicle_type_id', $vehicleTypeId)
                ->whereDate('valid_from', '<=', $travelDate)
                ->whereDate('valid_to', '>=', $travelDate)
                ->orderByDesc('valid_from')
                ->first()
        );

        if (!$rate) {
            throw new DomainException('No transport per-day rate found.');
        }

        $emptyRun = max(0, (float) ($payload['empty_run'] ?? 0));
        $deadLeg = max(0, (float) ($payload['dead_leg'] ?? 0));

        $base = ((float) $rate->rate * $days * $vehicles) + $emptyRun + $deadLeg;

        return [
            'type' => 'transport',
            'label' => 'Transport per-day rate matched',
            'base_total' => round($base, 2),
            'breakdown' => [
                'daily_rate' => round((float) $rate->rate, 2),
                'days' => $days,
                'vehicles' => $vehicles,
                'empty_run' => round($emptyRun, 2),
                'dead_leg' => round($deadLeg, 2),
            ],
            'item' => [
                'type' => 'transport',
                'reference_id' => $rate->id,
                'reference_source' => 'transport_rate',
                'quantity' => $days,
                'meta' => [
                    'manual_cost' => round($base, 2),
                    'days' => $days,
                    'vehicles' => $vehicles,
                    'empty_run' => $emptyRun,
                    'dead_leg' => $deadLeg,
                ],
            ],
        ];
    }

    private function quoteParkFee(array $payload, array $globals): array
    {
        $destinationId = (int) ($payload['destination_id'] ?? 0);
        $travelDate = (string) ($payload['date'] ?? now()->toDateString());
        $residentType = (string) ($payload['pax_type'] ?? $globals['resident_type']);
        $days = max(1, (int) ($payload['days'] ?? 1));

        $fee = Cache::remember(
            'pricing:park-fee:' . md5(json_encode([$destinationId, $travelDate, $residentType])),
            now()->addMinutes(15),
            function () use ($destinationId, $travelDate) {
                return ParkFee::query()
                    ->where('destination_id', $destinationId)
                    ->where(function ($q) use ($travelDate) {
                        $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $travelDate);
                    })
                    ->where(function ($q) use ($travelDate) {
                        $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', $travelDate);
                    })
                    ->orderBy('season_name')
                    ->first();
            }
        );

        if (!$fee) {
            throw new DomainException('No park fee found for selected destination/date.');
        }

        $adultRateCol = $residentType . '_adult';
        $childRateCol = $residentType . '_child';

        $adultRate = (float) ($fee->{$adultRateCol} ?? $fee->nr_adult);
        $childRate = (float) ($fee->{$childRateCol} ?? $fee->nr_child);

        $adults = (int) $globals['adults'];
        $teens = (int) $globals['teens'];
        $children = (int) $globals['children'];

        $base = ((($adults + $teens) * $adultRate) + ($children * $childRate)) * $days;

        return [
            'type' => 'park_fee',
            'label' => 'Park fee matched',
            'base_total' => round($base, 2),
            'breakdown' => [
                'resident_type' => $residentType,
                'adult_rate' => round($adultRate, 2),
                'child_rate' => round($childRate, 2),
                'adults' => $adults,
                'teens' => $teens,
                'children' => $children,
                'days' => $days,
            ],
            'item' => [
                'type' => 'park_fee',
                'reference_id' => $fee->id,
                'reference_source' => 'destination_fee',
                'quantity' => $days,
                'meta' => [
                    'manual_cost' => round($base, 2),
                    'resident_type' => $residentType,
                ],
            ],
        ];
    }

    private function quotePackage(Itinerary $itinerary, array $payload, array $globals): array
    {
        $package = Package::query()
            ->where('company_id', $itinerary->company_id)
            ->where('is_active', true)
            ->find((int) ($payload['package_id'] ?? 0));

        if (!$package) {
            throw new DomainException('Package not found or inactive.');
        }

        $paxTotal = max(1, (int) ($payload['pax_total'] ?? $globals['pax_total']));

        $base = $package->price_mode === 'per_group'
            ? (float) $package->base_price
            : (float) $package->base_price * $paxTotal;

        return [
            'type' => 'package',
            'label' => 'Package auto pricing matched',
            'base_total' => round($base, 2),
            'breakdown' => [
                'package_id' => $package->id,
                'price_mode' => $package->price_mode,
                'base_price' => round((float) $package->base_price, 2),
                'nights' => (int) $package->nights,
                'pax_total' => $paxTotal,
            ],
            'item' => [
                'type' => 'extra',
                'reference_id' => $package->id,
                'reference_source' => 'package',
                'quantity' => 1,
                'meta' => [
                    'manual_cost' => round($base, 2),
                    'label' => $package->name,
                    'package_id' => $package->id,
                    'nights' => (int) $package->nights,
                    'currency' => $package->currency,
                    'pax_total' => $paxTotal,
                ],
            ],
        ];
    }

    private function quoteExtra(array $payload, array $globals): array
    {
        $adults = (int) $globals['adults'];
        $teens = (int) $globals['teens'];
        $children = (int) $globals['children'];
        $qty = max(1, (int) ($payload['quantity'] ?? 1));

        $adultRate = (float) ($payload['adult_rate'] ?? 0);
        $teenRate = (float) ($payload['teen_rate'] ?? 0);
        $childRate = (float) ($payload['child_rate'] ?? 0);

        if ($adultRate > 0 || $teenRate > 0 || $childRate > 0) {
            $base = (($adults * $adultRate) + ($teens * $teenRate) + ($children * $childRate)) * $qty;

            return [
                'type' => 'extra',
                'label' => 'Extras pricing matched',
                'base_total' => round($base, 2),
                'breakdown' => [
                    'adults' => $adults,
                    'teens' => $teens,
                    'children' => $children,
                    'adult_rate' => round($adultRate, 2),
                    'teen_rate' => round($teenRate, 2),
                    'child_rate' => round($childRate, 2),
                    'quantity' => $qty,
                ],
                'item' => [
                    'type' => 'extra',
                    'reference_id' => 0,
                    'reference_source' => 'extra_matrix',
                    'quantity' => $qty,
                    'meta' => [
                        'manual_cost' => round($base, 2),
                    ],
                ],
            ];
        }

        $activityId = (int) ($payload['activity_id'] ?? 0);
        if ($activityId > 0) {
            $activity = Activity::find($activityId);
            if (! $activity) {
                throw new DomainException('Activity not found.');
            }

            $base = (float) $activity->price_per_person * max(1, $globals['pax_total']) * $qty;

            return [
                'type' => 'extra',
                'label' => 'Activity pricing matched',
                'base_total' => round($base, 2),
                'breakdown' => [
                    'activity_id' => $activity->id,
                    'rate_per_person' => round((float) $activity->price_per_person, 2),
                    'pax_total' => $globals['pax_total'],
                    'quantity' => $qty,
                ],
                'item' => [
                    'type' => 'activity',
                    'reference_id' => $activity->id,
                    'reference_source' => 'activity',
                    'quantity' => $qty,
                    'meta' => [
                        'manual_cost' => round($base, 2),
                    ],
                ],
            ];
        }

        $extra = Extra::find((int) ($payload['extra_id'] ?? 0));
        if (! $extra) {
            throw new DomainException('Extra item not found.');
        }

        $base = (float) $extra->price * $qty;

        return [
            'type' => 'extra',
            'label' => 'Extra pricing matched',
            'base_total' => round($base, 2),
            'breakdown' => [
                'extra_id' => $extra->id,
                'unit_price' => round((float) $extra->price, 2),
                'quantity' => $qty,
            ],
            'item' => [
                'type' => 'extra',
                'reference_id' => $extra->id,
                'reference_source' => 'extra',
                'quantity' => $qty,
                'meta' => [
                    'manual_cost' => round($base, 2),
                ],
            ],
        ];
    }

    private function applyServiceAdjustments(string $serviceType, float $baseTotal, array $pricingRules, int $paxTotal): array
    {
        $baseTotal = round(max(0, $baseTotal), 2);

        $markup = 0.0;
        if ($this->isCovered($serviceType, $pricingRules['markup_covers'])) {
            $markup = $pricingRules['markup_type'] === 'fixed'
                ? ((float) $pricingRules['markup_value'] * max(1, $paxTotal))
                : ($baseTotal * ((float) $pricingRules['markup_value'] / 100));
        }

        $discount = 0.0;
        if ($this->isCovered($serviceType, $pricingRules['discount_covers'])) {
            $discount = $pricingRules['discount_type'] === 'fixed'
                ? (float) $pricingRules['discount_value']
                : ($pricingRules['discount_type'] === 'percent'
                    ? ($baseTotal * ((float) $pricingRules['discount_value'] / 100))
                    : 0.0);
        }

        $final = round(max(0, $baseTotal + $markup - $discount), 2);

        return [
            'markup_amount' => round($markup, 2),
            'discount_amount' => round($discount, 2),
            'final_total' => $final,
        ];
    }

    private function normalizeGlobals(Itinerary $itinerary, array $globals): array
    {
        $adults = max(0, (int) ($globals['adults'] ?? $globals['pax']['adults'] ?? $itinerary->number_of_people));
        $teens = max(0, (int) ($globals['teens'] ?? $globals['pax']['teens'] ?? 0));
        $children = max(0, (int) ($globals['children'] ?? $globals['pax']['children'] ?? 0));

        return [
            'adults' => $adults,
            'teens' => $teens,
            'children' => $children,
            'child_ages' => (array) ($globals['child_ages'] ?? []),
            'pax_total' => max(1, $adults + $teens + $children),
            'resident_type' => (string) ($globals['resident_type'] ?? 'non_resident'),
            'currency' => (string) ($globals['currency'] ?? 'USD'),
            'year' => $globals['year'] ?? null,
            'season' => $globals['season'] ?? null,
            'room_rows' => (array) ($globals['room_rows'] ?? []),
        ];
    }

    private function normalizePricingRules(array $pricingRules, array $globals): array
    {
        $markupCovers = (array) ($pricingRules['markup_covers'] ?? ['accommodation', 'park_fee', 'flight', 'transfer', 'transport', 'extra', 'package']);
        $discountCovers = (array) ($pricingRules['discount_covers'] ?? $markupCovers);

        return [
            'markup_type' => (string) ($pricingRules['markup_type'] ?? 'percent'),
            'markup_value' => (float) ($pricingRules['markup_value'] ?? $pricingRules['markup_percent'] ?? 0),
            'markup_covers' => $markupCovers,
            'discount_type' => (string) ($pricingRules['discount_type'] ?? 'none'),
            'discount_value' => (float) ($pricingRules['discount_value'] ?? 0),
            'discount_covers' => $discountCovers,
            'vat_enabled' => (bool) ($pricingRules['vat_enabled'] ?? false),
            'vat_percent' => (float) ($pricingRules['vat_percent'] ?? 18),
        ];
    }

    private function validatePaxConsistency(array $globals): void
    {
        $rows = $globals['room_rows'];
        if (!is_array($rows) || $rows === []) {
            return;
        }

        $sumAdults = 0;
        $sumTeens = 0;
        $sumChildren = 0;

        foreach ($rows as $row) {
            $sumAdults += max(0, (int) ($row['adults'] ?? 0));
            $sumTeens += max(0, (int) ($row['teens'] ?? 0));
            $sumChildren += max(0, (int) ($row['children'] ?? 0));
        }

        if ($sumAdults + $sumTeens + $sumChildren <= 0) {
            throw new DomainException('Pax must match room setup.');
        }

        if ($sumAdults !== $globals['adults'] || $sumTeens !== $globals['teens'] || $sumChildren !== $globals['children']) {
            throw new DomainException('Pax must match room setup.');
        }
    }

    private function validateAccommodationOverlap(Itinerary $itinerary, string $arrivalDate, int $nights): void
    {
        $newStart = Carbon::parse($arrivalDate)->startOfDay();
        $newEnd = $newStart->copy()->addDays(max(1, $nights) - 1)->endOfDay();

        $itinerary->loadMissing('days.items');

        foreach ($itinerary->days as $day) {
            foreach ($day->items as $item) {
                if ($item->type !== 'hotel') {
                    continue;
                }

                $existingArrival = data_get($item->meta, 'arrival_date');
                $existingNights = (int) data_get($item->meta, 'nights', $item->quantity ?: 1);

                if (!$existingArrival) {
                    continue;
                }

                $existingStart = Carbon::parse($existingArrival)->startOfDay();
                $existingEnd = $existingStart->copy()->addDays(max(1, $existingNights) - 1)->endOfDay();

                $overlap = $newStart->lte($existingEnd) && $newEnd->gte($existingStart);
                if ($overlap) {
                    throw new DomainException('Accommodation dates overlap with an existing stay.');
                }
            }
        }
    }

    private function isCovered(string $serviceType, array $covers): bool
    {
        if ($covers === []) {
            return true;
        }

        return in_array($serviceType, $covers, true);
    }
}
