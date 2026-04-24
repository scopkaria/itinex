<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MasterData\TransportProvider;
use App\Models\MasterData\VehicleType;
use App\Models\MasterData\ProviderVehicle;
use App\Models\MasterData\TransportDriver;
use App\Models\MasterData\TransferRoute;
use App\Models\MasterData\TransportRateYear;
use App\Models\MasterData\TransportSeason;
use App\Models\MasterData\TransportRateType;
use App\Models\MasterData\TransportTransferRate;
use App\Models\MasterData\TransportPaymentPolicy;
use App\Models\MasterData\TransportCancellationPolicy;
use App\Models\MasterData\Destination;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransportProviderSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a company
        $company = Company::where('name', 'Sample Company')->first() 
            ?? Company::create([
                'name' => 'Sample Company',
                'email' => 'hello@sample.com',
                'phone' => '+255 123 456 789',
            ]);

        // Get destinations
        $nairobi = Destination::where('name', 'Nairobi')->first() 
            ?? Destination::create(['name' => 'Nairobi', 'code' => 'NBO', 'country_id' => 1]);
        $dar = Destination::where('name', 'Dar es Salaam')->first() 
            ?? Destination::create(['name' => 'Dar es Salaam', 'code' => 'DAR', 'country_id' => 1]);
        $kilimanjaro = Destination::where('name', 'Kilimanjaro')->first() 
            ?? Destination::create(['name' => 'Kilimanjaro', 'code' => 'KLM', 'country_id' => 1]);
        $zanzibar = Destination::where('name', 'Zanzibar')->first() 
            ?? Destination::create(['name' => 'Zanzibar', 'code' => 'ZNZ', 'country_id' => 1]);

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE TRANSPORT PROVIDER
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $transportProvider = TransportProvider::firstOrCreate(
            ['name' => 'Safari Express Limited'],
            [
                'company_id' => $company->id,
                'email' => 'info@safariexpress.co.tz',
                'phone' => '+255 654 321 987',
                'contact_person' => 'Jane Msangi',
                'description' => 'Premium safari transport services',
                'vat_type' => 'inclusive',
                'markup' => 20.00,
                'is_active' => true,
            ]
        );

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE VEHICLE TYPES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $vehicleTypes = [];
        $vehicleTypesData = [
            ['name' => '4x4 5-Seater', 'capacity' => 5, 'category' => 'safari'],
            ['name' => '4x4 7-Seater', 'capacity' => 7, 'category' => 'safari'],
            ['name' => 'Coaster', 'capacity' => 30, 'category' => 'transfer'],
            ['name' => 'Alphard', 'capacity' => 8, 'category' => 'luxury'],
            ['name' => 'Minibus', 'capacity' => 15, 'category' => 'transfer'],
            ['name' => 'Luggage Truck', 'capacity' => 1, 'category' => 'support'],
        ];

        foreach ($vehicleTypesData as $data) {
            $vehicleTypes[$data['name']] = VehicleType::firstOrCreate(
                ['transport_provider_id' => $transportProvider->id, 'name' => $data['name']],
                [
                    'capacity' => $data['capacity'],
                    'category' => $data['category'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE VEHICLES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $vehicles = [];
        $vehiclesData = [
            ['reg' => 'TZA-001', 'model' => '4x4 Land Cruiser', 'type' => '4x4 5-Seater', 'seats' => 5, 'fuel' => 'Diesel', 'consumption' => 12.5, 'scope' => 'safari'],
            ['reg' => 'TZA-002', 'model' => '4x4 Land Cruiser', 'type' => '4x4 7-Seater', 'seats' => 7, 'fuel' => 'Diesel', 'consumption' => 10.0, 'scope' => 'safari'],
            ['reg' => 'TZA-003', 'model' => 'Isuzu Coaster', 'type' => 'Coaster', 'seats' => 30, 'fuel' => 'Diesel', 'consumption' => 8.0, 'scope' => 'transfer'],
            ['reg' => 'TZA-004', 'model' => 'Toyota Alphard', 'type' => 'Alphard', 'seats' => 8, 'fuel' => 'Petrol', 'consumption' => 9.0, 'scope' => 'transfer'],
            ['reg' => 'TZA-005', 'model' => 'Mercedes Minibus', 'type' => 'Minibus', 'seats' => 15, 'fuel' => 'Diesel', 'consumption' => 7.5, 'scope' => 'both'],
        ];

        foreach ($vehiclesData as $data) {
            $vehicles[$data['reg']] = ProviderVehicle::firstOrCreate(
                [
                    'transport_provider_id' => $transportProvider->id,
                    'registration_number' => $data['reg'],
                ],
                [
                    'vehicle_type_id' => $vehicleTypes[$data['type']]->id,
                    'make_model' => $data['model'],
                    'fuel_type' => $data['fuel'],
                    'fuel_consumption_kmpl' => $data['consumption'],
                    'scope' => $data['scope'],
                    'status' => 'available',
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE DRIVERS
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $drivers = [];
        $driversData = [
            ['name' => 'Peter Mwangi', 'phone' => '+255 700 001 001', 'dob' => '1980-05-15', 'license' => 'CLK-001', 'license_type' => 'Class B', 'expiry' => '2027-12-31', 'skill' => 'expert', 'languages' => ['English', 'Swahili', 'French']],
            ['name' => 'David Simba', 'phone' => '+255 700 002 002', 'dob' => '1985-08-20', 'license' => 'CLK-002', 'license_type' => 'Class B', 'expiry' => '2027-06-30', 'skill' => 'pro', 'languages' => ['English', 'Swahili']],
            ['name' => 'Samuel Kipchoge', 'phone' => '+255 700 003 003', 'dob' => '1990-03-10', 'license' => 'CLK-003', 'license_type' => 'Class A', 'expiry' => '2026-09-15', 'skill' => 'pro', 'languages' => ['English', 'Swahili']],
            ['name' => 'Joseph Ndlela', 'phone' => '+255 700 004 004', 'dob' => '1988-12-25', 'license' => 'CLK-004', 'license_type' => 'Class B', 'expiry' => '2028-03-20', 'skill' => 'beginner', 'languages' => ['Swahili']],
        ];

        foreach ($driversData as $data) {
            $drivers[$data['name']] = TransportDriver::firstOrCreate(
                [
                    'transport_provider_id' => $transportProvider->id,
                    'name' => $data['name'],
                ],
                [
                    'phone' => $data['phone'],
                    'date_of_birth' => Carbon::parse($data['dob']),
                    'employment_date' => Carbon::now()->subYears(3),
                    'license_number' => $data['license'],
                    'license_type' => $data['license_type'],
                    'license_expiry' => Carbon::parse($data['expiry']),
                    'skill_level' => $data['skill'],
                    'languages' => json_encode($data['languages']),
                    'status' => 'active',
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE TRANSFER ROUTES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $routes = [];
        $routesData = [
            ['name' => 'Nairobi-Kilimanjaro', 'origin' => $nairobi, 'arrival' => $kilimanjaro, 'distance' => 350, 'duration' => 360],
            ['name' => 'Kilimanjaro-Zanzibar', 'origin' => $kilimanjaro, 'arrival' => $zanzibar, 'distance' => 680, 'duration' => 480],
            ['name' => 'Dar-Zanzibar', 'origin' => $dar, 'arrival' => $zanzibar, 'distance' => 65, 'duration' => 90],
            ['name' => 'Nairobi-Dar', 'origin' => $nairobi, 'arrival' => $dar, 'distance' => 1000, 'duration' => 1200],
            ['name' => 'Kilimanjaro-Dar', 'origin' => $kilimanjaro, 'arrival' => $dar, 'distance' => 350, 'duration' => 300],
        ];

        foreach ($routesData as $data) {
            $routes[$data['name']] = TransferRoute::firstOrCreate(
                [
                    'transport_provider_id' => $transportProvider->id,
                    'origin_destination_id' => $data['origin']->id,
                    'arrival_destination_id' => $data['arrival']->id,
                ],
                [
                    'distance_km' => $data['distance'],
                    'duration_minutes' => $data['duration'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE RATE YEARS
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $year2026 = TransportRateYear::firstOrCreate(
            ['transport_provider_id' => $transportProvider->id, 'year' => 2026],
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
            $seasons[$data['name']] = TransportSeason::firstOrCreate(
                [
                    'transport_provider_id' => $transportProvider->id,
                    'transport_rate_year_id' => $year2026->id,
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
            ['name' => 'Contract', 'markup_pct' => 2.00, 'markup_fixed' => 25, 'desc' => 'Long-term contract'],
            ['name' => 'Custom', 'markup_pct' => 10.00, 'markup_fixed' => 0, 'desc' => 'Custom negotiated rate'],
        ];

        foreach ($rateTypesData as $data) {
            $rateTypes[$data['name']] = TransportRateType::firstOrCreate(
                ['transport_provider_id' => $transportProvider->id, 'name' => $data['name']],
                [
                    'markup_percentage' => $data['markup_pct'],
                    'markup_fixed' => $data['markup_fixed'],
                    'description' => $data['desc'],
                ]
            );
        }

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // CREATE TRANSFER RATES (Buy vs Sell Price Matrix)
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        $ratesData = [
            // Nairobi-Kilimanjaro route
            ['route' => 'Nairobi-Kilimanjaro', 'vehicle' => '4x4 5-Seater', 'buy' => 250, 'sell' => 450, 'season' => 'Low'],
            ['route' => 'Nairobi-Kilimanjaro', 'vehicle' => '4x4 5-Seater', 'buy' => 300, 'sell' => 550, 'season' => 'High'],
            ['route' => 'Nairobi-Kilimanjaro', 'vehicle' => '4x4 5-Seater', 'buy' => 350, 'sell' => 650, 'season' => 'Peak'],

            // Kilimanjaro-Zanzibar route
            ['route' => 'Kilimanjaro-Zanzibar', 'vehicle' => 'Coaster', 'buy' => 1500, 'sell' => 2500, 'season' => 'Low'],
            ['route' => 'Kilimanjaro-Zanzibar', 'vehicle' => 'Coaster', 'buy' => 1800, 'sell' => 3000, 'season' => 'High'],

            // Dar-Zanzibar route
            ['route' => 'Dar-Zanzibar', 'vehicle' => 'Minibus', 'buy' => 80, 'sell' => 150, 'season' => 'Low'],
            ['route' => 'Dar-Zanzibar', 'vehicle' => 'Minibus', 'buy' => 100, 'sell' => 180, 'season' => 'High'],
        ];

        foreach ($ratesData as $data) {
            if (isset($routes[$data['route']]) && isset($vehicleTypes[$data['vehicle']]) && isset($seasons[$data['season']])) {
                TransportTransferRate::firstOrCreate(
                    [
                        'transport_provider_id' => $transportProvider->id,
                        'transfer_route_id' => $routes[$data['route']]->id,
                        'vehicle_type_id' => $vehicleTypes[$data['vehicle']]->id,
                        'transport_season_id' => $seasons[$data['season']]->id,
                    ],
                    [
                        'buy_price' => $data['buy'],
                        'sell_price' => $data['sell'],
                        'rate_type' => 'per_transfer',
                    ]
                );
            }
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
            TransportPaymentPolicy::firstOrCreate(
                [
                    'transport_provider_id' => $transportProvider->id,
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
            TransportCancellationPolicy::firstOrCreate(
                [
                    'transport_provider_id' => $transportProvider->id,
                    'days_before_travel' => $policy['days'],
                    'transport_season_id' => isset($seasons[$policy['season']]) ? $seasons[$policy['season']]->id : null,
                ],
                [
                    'penalty_percentage' => $policy['penalty'],
                ]
            );
        }

        echo "✅ Transport Provider seeded: {$transportProvider->name}\n";
        echo "   - {$transportProvider->vehicleTypes->count()} vehicle types\n";
        echo "   - {$transportProvider->vehicles->count()} vehicles\n";
        echo "   - {$transportProvider->drivers->count()} drivers\n";
        echo "   - {$transportProvider->transferRoutes->count()} routes\n";
        echo "   - {$transportProvider->transferRates->count()} transfer rates\n";
    }
}
