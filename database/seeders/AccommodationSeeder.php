<?php

namespace Database\Seeders;

use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1; // Safari Kings Ltd

        // Location mapping: data name → destination lookup name
        $locationMap = [
            'SERENGETI NATIONAL PARK'                  => 'Serengeti National Park',
            'KARATU'                                   => 'Karatu',
            'NGORONGORO CONSERVATION AREA AUTHORITY'   => 'Ngorongoro Conservation Area',
            'ARUSHA'                                   => 'Arusha',
            'ARUSHA NATIONAL PARK'                     => 'Arusha National Park',
            'TARANGIRE NATIONAL PARK'                  => 'Tarangire National Park',
            'LAKE MANYARA NATIONAL PARK'               => 'Lake Manyara National Park',
            'LAKE NATRON'                              => 'Lake Natron',
            'SELOUS GAME RESERVE'                      => 'Nyerere National Park',
            'NDUTU'                                    => 'Ndutu',
            'MOSHI'                                    => 'Moshi',
            'MIKUMI NATIONAL PARK'                     => 'Mikumi National Park',
            'RUAHA NATIONAL PARK'                      => 'Ruaha National Park',
        ];

        // Pre-resolve destination IDs; create missing ones on the fly
        $locationIds = [];
        foreach ($locationMap as $dataName => $destName) {
            $dest = Destination::whereRaw('LOWER(name) = ?', [strtolower($destName)])->first();
            if (!$dest) {
                $dest = Destination::create([
                    'company_id' => $companyId,
                    'name'       => $destName,
                    'country'    => 'Tanzania',
                ]);
            }
            $locationIds[$dataName] = $dest->id;
        }

        $records = [
            ['ACACIA BLISS SERENGETI', null, 'SERENGETI NATIONAL PARK'],
            ['ACACIA CENTRAL CAMP', 'ACACIA COLLECTION', 'SERENGETI NATIONAL PARK'],
            ['ACACIA FARM LODGE', null, 'KARATU'],
            ['ACACIA FARM LODGE KARATU', null, 'KARATU'],
            ['ACACIA MIGRATION', null, 'SERENGETI NATIONAL PARK'],
            ['ACACIA NGORONGORO LUXURY LODGE', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['ACACIA RETREAT ARUSHA', null, 'ARUSHA'],
            ['ACACIA SERONERA LUXURY', null, 'SERENGETI NATIONAL PARK'],
            ['ACACIA TARANGIRE LUXURY CAMP', 'ACACIA COLLECTION', 'TARANGIRE NATIONAL PARK'],
            ['AFRICA AMINI HILLSIDE RETREAT', null, 'ARUSHA NATIONAL PARK'],
            ['AFRICA AMINI TRADITIONAL MAASAI', null, 'ARUSHA'],
            ['AFRICA SAFARI ARUSHA', 'PARADISE AND WILDERNESS', 'ARUSHA'],
            ['AFRICA SAFARI KARATU', 'PARADISE AND WILDERNESS', 'KARATU'],
            ['AFRICA SAFARI LAKE MANYARA', 'PARADISE AND WILDERNESS', 'LAKE MANYARA NATIONAL PARK'],
            ['AFRICA SAFARI LAKE NATRON', 'PARADISE AND WILDERNESS', 'LAKE NATRON'],
            ['AFRICA SAFARI MAASAI BOMA', 'PARADISE AND WILDERNESS', 'SERENGETI NATIONAL PARK'],
            ['AFRICA SAFARI SELOUS', 'PARADISE AND WILDERNESS', 'SELOUS GAME RESERVE'],
            ['AFRICA SAFARI SERENGETI IKOMA', 'PARADISE AND WILDERNESS', 'SERENGETI NATIONAL PARK'],
            ['AFRICA SAFARI SOUTH SERENGETI', 'PARADISE AND WILDERNESS', 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['AIRPORT PLANET LODGE', 'PLANET LODGES', 'ARUSHA'],
            ['AMBURENI COFFEE LODGE', null, 'ARUSHA'],
            ['AMEG LODGE KILIMANJARO', null, 'MOSHI'],
            ['ANGALIA TENTED CAMP', null, 'MIKUMI NATIONAL PARK'],
            ['ANGATA KIMARISHE', null, 'SERENGETI NATIONAL PARK'],
            ['ANGATA MIGRATION CAMP', null, 'NDUTU'],
            ['ANGATA MIGRATION CAMP - BOLOGONJA', null, 'SERENGETI NATIONAL PARK'],
            ['ANGATA NGORONGORO', 'ANGATA CAMPS', 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['ANGATA SERENGETI CAMP', 'ANGATA CAMPS', 'SERENGETI NATIONAL PARK'],
            ['ANGATA TARANGIRE CAMP', 'ANGATA CAMPS', 'TARANGIRE NATIONAL PARK'],
            ['ARUMERU RIVER LODGE', null, 'ARUSHA'],
            ['ARUSHA COFFEE LODGE', null, 'ARUSHA'],
            ['ARUSHA PLANET LODGE', null, 'ARUSHA'],
            ['ARUSHA SAFARI LODGE', null, 'ARUSHA'],
            ['ARUSHA SERENA HOTEL', null, 'ARUSHA'],
            ['ASANJA MORU', 'ASANJA CAMPS', 'SERENGETI NATIONAL PARK'],
            ['ASANJA NDEMBO', null, 'RUAHA NATIONAL PARK'],
            ['ASANJA NYASIRORI WESTERN CORRIDOR', 'ASANJA', 'SERENGETI NATIONAL PARK'],
            ['ASANJA SIRI BUSTANI', null, 'SERENGETI NATIONAL PARK'],
        ];

        $now = now();

        foreach ($records as [$name, $chain, $location]) {
            $slug = Str::slug($name);

            // Skip if already exists (idempotent)
            if (Hotel::where('slug', $slug)->exists()) {
                continue;
            }

            Hotel::create([
                'company_id'  => $companyId,
                'name'        => $name,
                'slug'        => $slug,
                'chain'       => $chain,
                'location_id' => $locationIds[$location],
                'category'    => 'midrange',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
