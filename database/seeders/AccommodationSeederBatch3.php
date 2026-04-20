<?php

namespace Database\Seeders;

use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeederBatch3 extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $locationMap = [
            'ARUSHA'                                 => 'Arusha',
            'MOSHI'                                  => 'Moshi',
            'KARATU'                                 => 'Karatu',
            'KILIMANJARO MOUNTAIN AREA'              => 'Kilimanjaro Mountain Area',
            'SERENGETI NATIONAL PARK'                => 'Serengeti National Park',
            'ZANZIBAR'                               => 'Zanzibar',
            'TARANGIRE NATIONAL PARK'                => 'Tarangire National Park',
            'LAKE EYASI'                             => 'Lake Eyasi',
            'NGORONGORO CONSERVATION AREA AUTHORITY' => 'Ngorongoro Conservation Area',
            'LAKE MANYARA'                           => 'Lake Manyara National Park',
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
            ['KAHAWA HOUSE', null, 'ARUSHA'],
            ['KAMBI YA TEMBO', null, 'MOSHI'],
            ['KANKARI LODGE KARATU', null, 'KARATU'],
            ['KARANGA', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['KARATU TENTED LODGE', 'MAWE', 'KARATU'],
            ['KASKAZ MARA CAMP', 'NASIKIA CAMPS', 'SERENGETI NATIONAL PARK'],
            ['KATAMBUGA HOUSE', 'ENTARA CAMPS', 'ARUSHA'],
            ['KENDWA ROCK HOTEL', null, 'ZANZIBAR'],
            ['KENZAN CENTRAL TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KENZAN MIGRATION CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KEYS HOTEL MBOKOMU', null, 'MOSHI'],
            ['KEYS HOTEL URU ROAD', null, 'MOSHI'],
            ['KIA LODGE', null, 'ARUSHA'],
            ['KIBO HUT', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['KIBO PALACE HOTEL', null, 'ARUSHA'],
            ['KILELEWA CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['KILIVILLA', null, 'ARUSHA'],
            ['KILIMANJARO WONDERS HOTEL', null, 'MOSHI'],
            ['KILINDI ZANZIBAR', null, 'ZANZIBAR'],
            ['KIRAWIRA SERENA CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KIROCHE LUXURY CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KIRURUMU CENTRAL SERENGETI', null, 'SERENGETI NATIONAL PARK'],
            ['KIRURUMU MANYARA', null, 'LAKE MANYARA'],
            ['KIRURUMU NORTH SERENGETI CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KIRURUMU TARANGIRE LODGE', null, 'TARANGIRE NATIONAL PARK'],
            ['KISIMANGEDA TENTED CAMP', null, 'LAKE EYASI'],
            ['KISURA SERENGETI CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KITELA LODGE', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['KONTIKI SERENGETI CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KONTIKI KIMARESHE CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KUBU KUBU TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['KUDU LODGE', null, 'KARATU'],
            ['KUPAGA VILLAS BOUTIQUE HOTEL', null, 'ZANZIBAR'],
            ['KWANZA SUNRISE RESORT', null, 'ZANZIBAR'],
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
