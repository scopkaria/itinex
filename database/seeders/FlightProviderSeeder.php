<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\FlightRateYear;
use App\Models\MasterData\FlightSeason;
use App\Models\MasterData\FlightRateType;
use App\Models\MasterData\AircraftType;
use App\Models\MasterData\FlightRoute;
use App\Models\MasterData\Destination;
use App\Models\MasterData\FlightPaymentPolicy;
use App\Models\MasterData\FlightCancellationPolicy;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FlightProviderSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a company for flight providers
        $company = Company::where('name', 'Sample Company')->first() 
            ?? Company::create([
                'name' => 'Sample Company',
                'email' => 'hello@sample.com',
                'phone' => '+255 123 456 789',
            ]);

        // Get destinations (should exist from previous seeder)
        $nairobi = Destination::where('name', 'Nairobi')->first();
        $dar = Destination::where('name', 'Dar es Salaam')->first();
        $kilimanjaro = Destination::where('name', 'Kilimanjaro')->first();
        $zanzibar = Destination::where('name', 'Zanzibar')->first();

        if (!$nairobi || !$dar) {
            // Create destinations if they don't exist
            $nairobi = Destination::create(['name' => 'Nairobi', 'code' => 'NBO', 'country_id' => 1]);
            $dar = Destination::create(['name' => 'Dar es Salaam', 'code' => 'DAR', 'country_id' => 1]);
            $kilimanjaro = Destination::create(['name' => 'Kilimanjaro', 'code' => 'KLM', 'country_id' => 1]);
            $zanzibar = Destination::create(['name' => 'Zanzibar', 'code' => 'ZNZ', 'country_id' => 1]);
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE FLIGHT PROVIDER
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $flightProvider = FlightProvider::firstOrCreate(
            ['name' => 'Northern Air Tanzania'],
            [
                'company_id' => $company->id,
                'email' => 'info@northernair.co.tz',
                'phone' => '+255 787 654 321',
                'contact_person' => 'John Mwase',
                'description' => 'Leading regional airline in Tanzania',
                'vat_type' => 'inclusive',
                'markup' => 15.00,
                'is_active' => true,
            ]
        );

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE AIRCRAFT TYPES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $aircraft = [];
        $aircraftData = [
            ['name' => 'Cessna 208', 'capacity' => 9, 'description' => 'Single turbine, for safari transfers'],
            ['name' => 'Beechcraft 1900', 'capacity' => 19, 'description' => 'Twin-engine commuter'],
            ['name' => 'Dash 8-400', 'capacity' => 78, 'description' => 'Regional turboprop'],
        ];

        foreach ($aircraftData as $data) {
            $aircraft[$data['name']] = AircraftType::firstOrCreate(
                ['flight_provider_id' => $flightProvider->id, 'name' => $data['name']],
                [
                    'capacity' => $data['capacity'],
                    'description' => $data['description'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE FLIGHT ROUTES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $routes = [];
        $routesData = [
            ['origin' => $nairobi, 'arrival' => $kilimanjaro, 'duration' => 75],
            ['origin' => $kilimanjaro, 'arrival' => $zanzibar, 'duration' => 150],
            ['origin' => $dar, 'arrival' => $zanzibar, 'duration' => 60],
            ['origin' => $nairobi, 'arrival' => $dar, 'duration' => 180],
        ];

        foreach ($routesData as $data) {
            $routes["{$data['origin']->name}-{$data['arrival']->name}"] = FlightRoute::firstOrCreate(
                [
                    'flight_provider_id' => $flightProvider->id,
                    'origin_destination_id' => $data['origin']->id,
                    'arrival_destination_id' => $data['arrival']->id,
                ],
                [
                    'flight_duration_minutes' => $data['duration'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE RATE YEARS
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $year2026 = FlightRateYear::firstOrCreate(
            ['flight_provider_id' => $flightProvider->id, 'year' => 2026],
            [
                'valid_from' => Carbon::parse('2026-01-01'),
                'valid_to' => Carbon::parse('2026-12-31'),
                'status' => 'active',
            ]
        );

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE SEASONS
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $seasons = [];
        $seasonsData = [
            ['name' => 'Peak', 'start' => '2026-12-01', 'end' => '2026-12-31', 'order' => 1],
            ['name' => 'High', 'start' => '2026-06-01', 'end' => '2026-10-31', 'order' => 2],
            ['name' => 'Low', 'start' => '2026-01-01', 'end' => '2026-05-31', 'order' => 3],
        ];

        foreach ($seasonsData as $data) {
            $seasons[$data['name']] = FlightSeason::firstOrCreate(
                [
                    'flight_provider_id' => $flightProvider->id,
                    'flight_rate_year_id' => $year2026->id,
                    'name' => $data['name'],
                ],
                [
                    'start_date' => Carbon::parse($data['start']),
                    'end_date' => Carbon::parse($data['end']),
                    'display_order' => $data['order'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE RATE TYPES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $rateTypes = [];
        $rateTypesData = [
            ['name' => 'STO', 'markup_pct' => 5.00, 'markup_fixed' => 0, 'desc' => 'Standard IT Rate'],
            ['name' => 'Special', 'markup_pct' => 15.00, 'markup_fixed' => 0, 'desc' => 'Special negotiated rate'],
            ['name' => 'Contract', 'markup_pct' => 2.00, 'markup_fixed' => 50, 'desc' => 'Long-term contract rate'],
        ];

        foreach ($rateTypesData as $data) {
            $rateTypes[$data['name']] = FlightRateType::firstOrCreate(
                ['flight_provider_id' => $flightProvider->id, 'name' => $data['name']],
                [
                    'markup_percentage' => $data['markup_pct'],
                    'markup_fixed' => $data['markup_fixed'],
                    'description' => $data['desc'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE PAYMENT POLICIES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $paymentPolicies = [
            ['days' => 60, 'percentage' => 50],
            ['days' => 30, 'percentage' => 80],
            ['days' => 14, 'percentage' => 100],
        ];

        foreach ($paymentPolicies as $policy) {
            FlightPaymentPolicy::firstOrCreate(
                [
                    'flight_provider_id' => $flightProvider->id,
                    'days_before_arrival' => $policy['days'],
                ],
                [
                    'percentage_due' => $policy['percentage'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE CANCELLATION POLICIES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $cancellationPolicies = [
            ['days' => 60, 'penalty' => 10, 'season' => 'Low'],
            ['days' => 60, 'penalty' => 25, 'season' => 'High'],
            ['days' => 60, 'penalty' => 50, 'season' => 'Peak'],
            ['days' => 30, 'penalty' => 50, 'season' => null],
            ['days' => 14, 'penalty' => 100, 'season' => null],
        ];

        foreach ($cancellationPolicies as $policy) {
            FlightCancellationPolicy::firstOrCreate(
                [
                    'flight_provider_id' => $flightProvider->id,
                    'days_before_travel' => $policy['days'],
                    'flight_season_id' => isset($seasons[$policy['season']]) ? $seasons[$policy['season']]->id : null,
                ],
                [
                    'penalty_percentage' => $policy['penalty'],
                ]
            );
        }

        echo "✅ Flight Provider seeded: {$flightProvider->name}\n";
        echo "   - {$flightProvider->aircraftTypes->count()} aircraft types\n";
        echo "   - {$flightProvider->routes->count()} routes\n";
        echo "   - {$flightProvider->seasons->count()} seasons\n";
        echo "   - {$flightProvider->rateTypes->count()} rate types\n";
    }
}
