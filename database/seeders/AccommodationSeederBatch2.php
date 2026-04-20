<?php

namespace Database\Seeders;

use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeederBatch2 extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $locationMap = [
            'ZANZIBAR'                               => 'Zanzibar',
            'SERENGETI NATIONAL PARK'                => 'Serengeti National Park',
            'TARANGIRE NATIONAL PARK'                => 'Tarangire National Park',
            'KARATU'                                 => 'Karatu',
            'NGORONGORO CONSERVATION AREA AUTHORITY' => 'Ngorongoro Conservation Area',
            'DAR ES SALAAM'                          => 'Dar es Salaam',
            'ARUSHA'                                 => 'Arusha',
            'PANGANI'                                => 'Pangani',
            'LAKE MANYARA NATIONAL PARK'             => 'Lake Manyara National Park',
            'KILIMANJARO MOUNTAIN AREA'              => 'Kilimanjaro Mountain Area',
            'LAKE NATRON'                            => 'Lake Natron',
            'NAIROBI'                                => 'Nairobi',
        ];

        $locationIds = [];
        foreach ($locationMap as $dataName => $destName) {
            $dest = Destination::whereRaw('LOWER(name) = ?', [strtolower($destName)])->first();
            if (!$dest) {
                $dest = Destination::create([
                    'company_id' => $companyId,
                    'name'       => $destName,
                    'country'    => in_array($destName, ['Nairobi']) ? 'Kenya' : 'Tanzania',
                ]);
            }
            $locationIds[$dataName] = $dest->id;
        }

        $records = [
            // LETTER C
            ['CASA BEACH HOTEL', null, 'ZANZIBAR'],
            ['CONSERVE SAFARI', null, 'SERENGETI NATIONAL PARK'],
            ['CONSERVE SAFARI - TARANGIRE', null, 'TARANGIRE NATIONAL PARK'],
            ['COUNTRY LODGE', 'BOUGAINVILLEA LODGES', 'KARATU'],
            ['CRATER FOREST TENTED CAMP', null, 'KARATU'],
            ['CRATER RIM VIEW INN', null, 'KARATU'],
            ['CRATERS EDGE', 'WILDERNESS COLLECTION', 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            // LETTER D
            ['DANCING DUMA TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['DAR ES SALAAM SERENA HOTEL', null, 'DAR ES SALAAM'],
            ['DELTA HOTELS', null, 'DAR ES SALAAM'],
            ['DOUBLE VIEW', null, 'ARUSHA'],
            ['DREAM OF ZANZIBAR RESORT', 'PARADISE AND WILDERNESS', 'ZANZIBAR'],
            // LETTER E
            ['EILEENS TREES INN', null, 'KARATU'],
            ['ELEMENT BY WESTIN', null, 'DAR ES SALAAM'],
            ['ELEPHANT SPRING TARANGIRE', null, 'TARANGIRE NATIONAL PARK'],
            ['ELEWANA TARANGIRE TREETOPS', null, 'TARANGIRE NATIONAL PARK'],
            ['EMAYANI BEACH LODGE', null, 'PANGANI'],
            ['EMBALAKAI AUTHENTIC CAMP - SERENGETI', null, 'SERENGETI NATIONAL PARK'],
            ['EMBALAKAI NGORONGORO CAMP', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['EMPAKAI CAMP', null, 'KARATU'],
            ['ENDORO LODGE', null, 'KARATU'],
            ['ENGITERATA CAMPS', null, 'SERENGETI NATIONAL PARK'],
            ['ENVI SISINI MARA', 'ENVI SISINI', 'SERENGETI NATIONAL PARK'],
            ['ENVI SISINI SERENGETI', 'ENVI SISINI', 'SERENGETI NATIONAL PARK'],
            ['ENYATI LODGE', null, 'KARATU'],
            ['ESCARPMENT LUXURY LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            ['ESSQUE ZALU ZANZIBAR', null, 'ZANZIBAR'],
            // LETTER F
            ['FARM HOUSE VALLEY', null, 'KARATU'],
            ['FARM OF DREAMS LODGE', null, 'KARATU'],
            ['FIRST CAVE CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['FOREST HILL LODGE', null, 'ARUSHA'],
            ['FOUR POINT SHERATON', null, 'ARUSHA'],
            ['FOUR SEASONS SERENGETI', null, 'SERENGETI NATIONAL PARK'],
            ['FREDDIE MERCURY APARTMENTS', null, 'ZANZIBAR'],
            ['FUMBA BEACH LODGE', null, 'ZANZIBAR'],
            ['FUN BEACH HOTEL', null, 'ZANZIBAR'],
            ['FUN RETREAT RESORT', null, 'ARUSHA'],
            ['FUNDU LAGOON PEMBA', null, 'ZANZIBAR'],
            // LETTER G
            ['GIRAFFE MANOR HOTEL', null, 'NAIROBI'],
            ['GOL CAMP', null, 'LAKE NATRON'],
            ['GOLD ZANZIBAR', null, 'ZANZIBAR'],
            ['GOLDEN SAFARI CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['GOLDEN TULIP ZANZIBAR RESORT', null, 'ZANZIBAR'],
            ['GRAN MELIA ARUSHA', null, 'ARUSHA'],
            // LETTER H
            ['HAKUNA MATATA SAFARI LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['HARMONY SAFARI CAMPS', null, 'SERENGETI NATIONAL PARK'],
            ['HEART AND SOUL LODGE', null, 'KARATU'],
            ['HIGH VIEW HOTEL', null, 'KARATU'],
            ['HIGHVIEW COFFEE LODGE', null, 'KARATU'],
            ['HIPPO TRAILS CAMP', 'BOUGAINVILLEA LODGES', 'SERENGETI NATIONAL PARK'],
            ['HOROMBO CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            // LETTER I
            ['IKOMA TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['IKOMA WILD CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['INTO THE WILD', null, 'SERENGETI NATIONAL PARK'],
            ['INTO WILD AFRICA TENTED CAMPS LTD', null, 'SERENGETI NATIONAL PARK'],
            // LETTER J
            ['JAFFERJI HOUSE', null, 'ZANZIBAR'],
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
