<?php

namespace Database\Seeders;

use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeederBatch6 extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $locationMap = [
            'KILIMANJARO MOUNTAIN AREA'  => 'Kilimanjaro Mountain Area',
            'ARUSHA'                     => 'Arusha',
            'ZANZIBAR'                   => 'Zanzibar',
            'SERENGETI NATIONAL PARK'    => 'Serengeti National Park',
            'MOSHI'                      => 'Moshi',
            'KILIMANJARO NATIONAL PARK'  => 'Kilimanjaro National Park',
            'NAIROBI'                    => 'Nairobi',
            'LAKE NATRON'                => 'Lake Natron',
            'KARATU'                     => 'Karatu',
        ];

        $locationIds = [];
        foreach ($locationMap as $dataName => $destName) {
            $dest = Destination::whereRaw('LOWER(name) = ?', [strtolower($destName)])->first();
            if (!$dest) {
                $country = in_array($destName, ['Nairobi']) ? 'Kenya' : 'Tanzania';
                $dest = Destination::create([
                    'company_id' => $companyId,
                    'name'       => $destName,
                    'country'    => $country,
                ]);
            }
            $locationIds[$dataName] = $dest->id;
        }

        $records = [
            // LETTER U
            ['UHURU PEAK', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['UNDER THE SHADE', null, 'ARUSHA'],
            // LETTER V
            ['VILLA KIVA BOUTIQUE HOTEL', null, 'ZANZIBAR'],
            // LETTER W
            ['WARANGI RIDGE', null, 'SERENGETI NATIONAL PARK'],
            ['WERUWERU RIVER LODGE NEW', null, 'MOSHI'],
            ['WEST KILI CAMP', null, 'KILIMANJARO NATIONAL PARK'],
            ['WESTON HOTEL', null, 'NAIROBI'],
            ['WOGA CAMPSITE', null, 'LAKE NATRON'],
            ['WOODLANDS SERENGETI', null, 'SERENGETI NATIONAL PARK'],
            // LETTER Z
            ['ZANZIBAR BAY RESORT', 'PARADISE AND WILDERNESS', 'ZANZIBAR'],
            ['ZANZIBAR BEACH RESORT', 'WELLWORTH HOTELS', 'ZANZIBAR'],
            ['ZANZIBAR SERENA HOTEL', 'SERENA HOTELS', 'ZANZIBAR'],
            ['ZIWANI LODGE', null, 'KARATU'],
            ['ZURI ZANZIBAR HOTEL', null, 'ZANZIBAR'],
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
