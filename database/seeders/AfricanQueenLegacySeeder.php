<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MasterData\Extra;
use App\Models\MasterData\FlightPolicy;
use App\Models\MasterData\FlightProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AfricanQueenLegacySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['email' => 'info@africanqueenadventures.com'],
            [
                'name' => 'African Queen Adventures',
                'phone' => '+255 789 883 040',
                'address' => "info@africanqueenadventures.com\nwww.africanqueenadventures.com\n+255 789 883 040",
                'is_active' => true,
            ]
        );

        $this->seedForCompany($company);
    }

    public function seedForCompany(Company $company): void
    {
        $companyData = [
            'name' => 'African Queen Adventures',
            'email' => 'info@africanqueenadventures.com',
            'phone' => '+255 789 883 040',
            'address' => "info@africanqueenadventures.com\nwww.africanqueenadventures.com\n+255 789 883 040",
            'is_active' => true,
        ];

        foreach (['enable_flights', 'enable_transport', 'enable_activities', 'enable_advanced_rates'] as $column) {
            if (Schema::hasColumn('companies', $column)) {
                $companyData[$column] = true;
            }
        }

        $company->update($companyData);

        $this->seedUsers($company);
        $this->seedExtras($company);
        $this->seedFlightProviders($company);
    }

    private function seedUsers(Company $company): void
    {
        $users = [
            [
                'name' => 'Assen Hitov',
                'email' => 'asen@emeraldbg.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Sanjay Lakhtaria',
                'email' => 'sanjay.lakhtaria@gttnt.com',
                'role' => User::ROLE_STAFF,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'company_id' => $company->id,
                    'name' => $userData['name'],
                    'password' => 'password',
                    'role' => $userData['role'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedExtras(Company $company): void
    {
        $extras = [
            ['name' => 'Arusha City Tour', 'adult' => 0, 'teen' => 0, 'child' => 0, 'vehicle' => 100, 'group' => 0],
            ['name' => 'Ballon Ride', 'adult' => 540, 'teen' => 540, 'child' => 540, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Canope Walk Manyara', 'adult' => 54, 'teen' => 54, 'child' => 12, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Cultural Tour Mto Wa Mbu', 'adult' => 25, 'teen' => 25, 'child' => 15, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Dinner Arusha Serena Hotel', 'adult' => 50, 'teen' => 50, 'child' => 50, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Drinking Water', 'adult' => 1, 'teen' => 1, 'child' => 1, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Hotspring', 'adult' => 20, 'teen' => 20, 'child' => 20, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Kili Day Hike', 'adult' => 120, 'teen' => 120, 'child' => 120, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Lake Eyasi', 'adult' => 0, 'teen' => 0, 'child' => 0, 'vehicle' => 170, 'group' => 0],
            ['name' => 'Local Food', 'adult' => 20, 'teen' => 20, 'child' => 20, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Lunch At Arusha Coffee Lodge', 'adult' => 40, 'teen' => 40, 'child' => 40, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Lunch Box', 'adult' => 15, 'teen' => 15, 'child' => 15, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Maasai Boma Visit', 'adult' => 0, 'teen' => 0, 'child' => 0, 'vehicle' => 50, 'group' => 0],
            ['name' => 'Marangu Water Fall', 'adult' => 25, 'teen' => 25, 'child' => 25, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Materuni Cultural Tour', 'adult' => 25, 'teen' => 25, 'child' => 15, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Mkomazi Rhino Project', 'adult' => 40, 'teen' => 40, 'child' => 40, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Office Running Charges', 'adult' => 30, 'teen' => 30, 'child' => 0, 'vehicle' => 0, 'group' => 30],
            ['name' => 'Olduvai', 'adult' => 37, 'teen' => 37, 'child' => 37, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Olduvai Gorge', 'adult' => 36, 'teen' => 36, 'child' => 36, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Olpopongi', 'adult' => 59, 'teen' => 59, 'child' => 59, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Visa', 'adult' => 200, 'teen' => 200, 'child' => 200, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Walking In Park', 'adult' => 24, 'teen' => 24, 'child' => 24, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Walking Safari - Arusha National Park', 'adult' => 24, 'teen' => 24, 'child' => 12, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Watron', 'adult' => 85, 'teen' => 85, 'child' => 85, 'vehicle' => 0, 'group' => 0],
            ['name' => 'WMA', 'adult' => 30, 'teen' => 30, 'child' => 30, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Zanzibar Visa', 'adult' => 200, 'teen' => 200, 'child' => 200, 'vehicle' => 0, 'group' => 0],
            ['name' => 'Zip Line', 'adult' => 65, 'teen' => 65, 'child' => 35, 'vehicle' => 0, 'group' => 0],
        ];

        foreach ($extras as $extra) {
            foreach ($this->normalizeExtraPrices($extra) as $normalizedExtra) {
                Extra::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $normalizedExtra['name'],
                    ],
                    ['price' => $normalizedExtra['price']]
                );
            }
        }
    }

    private function normalizeExtraPrices(array $extra): array
    {
        $normalized = [];
        $personRates = array_filter([
            'Adult' => (float) $extra['adult'],
            'Teen' => (float) $extra['teen'],
            'Child' => (float) $extra['child'],
        ], fn (float $value) => $value > 0);

        if ($personRates !== []) {
            $uniqueRates = array_values(array_unique(array_values($personRates)));

            if (count($uniqueRates) === 1) {
                $normalized[] = [
                    'name' => $extra['name'],
                    'price' => $uniqueRates[0],
                ];
            } else {
                foreach ($personRates as $label => $value) {
                    $normalized[] = [
                        'name' => sprintf('%s (%s)', $extra['name'], $label),
                        'price' => $value,
                    ];
                }
            }
        }

        if ((float) $extra['vehicle'] > 0) {
            $normalized[] = [
                'name' => sprintf('%s (Per Vehicle)', $extra['name']),
                'price' => (float) $extra['vehicle'],
            ];
        }

        if ((float) $extra['group'] > 0) {
            $normalized[] = [
                'name' => sprintf('%s (Per Group)', $extra['name']),
                'price' => (float) $extra['group'],
            ];
        }

        return $normalized;
    }

    private function seedFlightProviders(Company $company): void
    {
        $providers = [
            [
                'name' => 'Auric Air',
                'policies' => [
                    [
                        'title' => 'Legacy child age rules (2026)',
                        'content' => "0-2 years: Free of charge\n2-11 years: Child Rate",
                    ],
                    [
                        'title' => 'Legacy supported destinations',
                        'content' => $this->legacyFlightDestinations(),
                    ],
                ],
            ],
            [
                'name' => 'Flight Link',
                'policies' => [
                    [
                        'title' => 'Legacy child age rules (2026)',
                        'content' => "0-1 years: Free of charge\n2-10 years: Child Rate\n11+ years: Adult Rate",
                    ],
                    [
                        'title' => 'Legacy supported destinations',
                        'content' => $this->legacyFlightDestinations(),
                    ],
                ],
            ],
        ];

        foreach ($providers as $providerData) {
            $provider = FlightProvider::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $providerData['name'],
                ],
                [
                    'email' => null,
                    'phone' => null,
                    'contact_person' => null,
                    'description' => 'Imported from the African Queen legacy export.',
                    'vat_type' => 'inclusive',
                    'markup' => 0,
                    'is_active' => true,
                ]
            );

            foreach ($providerData['policies'] as $policyData) {
                FlightPolicy::updateOrCreate(
                    [
                        'flight_provider_id' => $provider->id,
                        'policy_type' => 'general',
                        'title' => $policyData['title'],
                    ],
                    ['content' => $policyData['content']]
                );
            }
        }
    }

    private function legacyFlightDestinations(): string
    {
        return implode("\n", [
            'Arusha',
            'Zanzibar',
            'Dar',
            'Entebe (UG)',
            'Chem Chem',
            'Mafia',
            'Ifakara',
            'Iringa',
            'Ruaha',
            'Pangani',
            'Pemba',
            'Dodoma',
            'Kogatende',
            'Fort Ikoma',
            'Mombasa',
            'Migori (KE)',
            'Tarime',
            'Tanga',
            'Kilimanjaro',
            'Ndutu',
            'Lobo',
            'Grumeti',
            'Nairobi, Wilson',
            'Ngorongoro',
            'Manyara',
            'Maasai Mara',
            'Moshi',
            'Wasso',
            'Mwiba',
            'Kigali',
            'Rubondo',
            'Lamai',
            'Tarangire Kuro',
            'Dolly for Kiligolf',
            'Mwanza',
            'Singita',
            'Songo Songo',
            'Serengeti South',
            'SGS',
            'Saadani',
            'Sasakwa',
            'Selous',
            'Serengeti',
            'Seronera',
        ]);
    }
}