<?php

namespace Database\Seeders;

use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeederBatch5 extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $locationMap = [
            'SERENGETI NATIONAL PARK'   => 'Serengeti National Park',
            'MIKUMI NATIONAL PARK'      => 'Mikumi National Park',
            'KARATU'                    => 'Karatu',
            'TARANGIRE NATIONAL PARK'   => 'Tarangire National Park',
            'ZANZIBAR'                  => 'Zanzibar',
            'ARUSHA'                    => 'Arusha',
            'MOSHI'                     => 'Moshi',
            'KILIMANJARO MOUNTAIN AREA' => 'Kilimanjaro Mountain Area',
            'LAKE EYASI'                => 'Lake Eyasi',
        ];

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
            ['TAMBA TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['TAN-SWISS LODGE', null, 'MIKUMI NATIONAL PARK'],
            ['TANZANICE FARM LODGE', null, 'KARATU'],
            ['TARANGILE KORONGO CAMP', null, 'TARANGIRE NATIONAL PARK'],
            ['TARANGIRE KATI KATI TENTED CAMP', null, 'TARANGIRE NATIONAL PARK'],
            ['TARANGIRE KURO TREE TOPS', null, 'TARANGIRE NATIONAL PARK'],
            ['TARANGIRE LUXURY HIDEAWAY', null, 'TARANGIRE NATIONAL PARK'],
            ['TARANGIRE NDOVU TENTED LODGE', 'NASIKIA CAMPS', 'TARANGIRE NATIONAL PARK'],
            ['TARANGIRE SAFARI LODGE', null, 'TARANGIRE NATIONAL PARK'],
            ['TARANGIRE SIMBA LODGE', 'SIMBA PORTFOLIO', 'TARANGIRE NATIONAL PARK'],
            ['TARANGIRE SOPA LODGE', 'SOPA LODGES', 'TARANGIRE NATIONAL PARK'],
            ['TEMBO APARTMENTS', null, 'ZANZIBAR'],
            ['TEMBO HOUSE HOTEL AND APARTMENTS', null, 'ZANZIBAR'],
            ['TEMBO KIWENGWA RESORT', null, 'ZANZIBAR'],
            ['TEMBO PALACE HOTEL', null, 'ZANZIBAR'],
            ['THE ROYAL ZANZIBAR BEACH RESORT', null, 'ZANZIBAR'],
            ['THE AFRICAN TULIP', null, 'ARUSHA'],
            ['THE CASTLE NGORONGORO', null, 'KARATU'],
            ['THE LOOP BEACH RESORT', null, 'ZANZIBAR'],
            ['THE MANOR AT NGORONGORO', null, 'KARATU'],
            ['THE MARIDADI HOTEL', null, 'MOSHI'],
            ['THE MORA', null, 'ZANZIBAR'],
            ['THE RESIDENCE ZANZIBAR', null, 'ZANZIBAR'],
            ['THE RETREAT AT NGORONGORO', null, 'KARATU'],
            ['THE SINGING GRASS', null, 'SERENGETI NATIONAL PARK'],
            ['THIRD CAVE CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['THORN TREE CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['TINDIGA TENTED CAMP', null, 'LAKE EYASI'],
            ['TLOMA LODGE', null, 'KARATU'],
            ['TOA HOTEL AND SPA', null, 'ZANZIBAR'],
            ['TUI BLUE BAHARI ZANZIBAR', null, 'ZANZIBAR'],
            ['TUKAONE SERENGETI CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['TULIA BOUTIQUE HOTEL AND SPA', null, 'ARUSHA'],
            ['TULIA RETREAT HOTEL AND SPA', null, 'ARUSHA'],
            ['TURACO NGORONGORO VALLEY', null, 'KARATU'],
        ];

        $inserted = 0;
        $skipped = 0;
        $now = now();

        foreach ($records as [$name, $chain, $location]) {
            $slug = Str::slug($name);

            if (Hotel::where('slug', $slug)->exists()) {
                $skipped++;
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
            $inserted++;
        }

        $this->command->info("Inserted: {$inserted}, Skipped (duplicates): {$skipped}");
    }
}
