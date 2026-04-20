<?php

namespace Database\Seeders;

use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeederBatch4 extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $locationMap = [
            'SERENGETI NATIONAL PARK'                => 'Serengeti National Park',
            'TARANGIRE NATIONAL PARK'                => 'Tarangire National Park',
            'ARUSHA'                                 => 'Arusha',
            'LAKE EYASI'                             => 'Lake Eyasi',
            'LAKE MANYARA NATIONAL PARK'             => 'Lake Manyara National Park',
            'NDUTU'                                  => 'Ndutu',
            'LAKE NATRON'                            => 'Lake Natron',
            'ZANZIBAR'                               => 'Zanzibar',
            'KARATU'                                 => 'Karatu',
            'MOSHI'                                  => 'Moshi',
            'KILIMANJARO MOUNTAIN AREA'              => 'Kilimanjaro Mountain Area',
            'NGORONGORO CONSERVATION AREA AUTHORITY' => 'Ngorongoro Conservation Area',
            'MIKUMI NATIONAL PARK'                   => 'Mikumi National Park',
            'ARUSHA NATIONAL PARK'                   => 'Arusha National Park',
            'SAADANI NATIONAL PARK'                  => 'Saadani National Park',
            'KILIMANJARO NATIONAL PARK'              => 'Kilimanjaro National Park',
            'SELOUS GAME RESERVE'                    => 'Nyerere National Park',
            'MASAI MARA GAME RESERVE'                => 'Masai Mara National Park',
            'Masai Mara Game Reserve'                => 'Masai Mara National Park',
            'Nyerere National Park'                  => 'Nyerere National Park',
            // New locations
            'MOROGORO'                               => 'Morogoro',
            'LOBO'                                   => 'Lobo',
            'MWANZA'                                 => 'Mwanza',
            'MTO WA MBU'                             => 'Mto wa Mbu',
            'KILIMANJARO'                            => 'Kilimanjaro Mountain Area',
            'PEMBA ISLAND'                           => 'Pemba Island',
            'USA RIVER ARUSHA'                       => 'Usa River',
            'LAKE VICTORIA'                          => 'Lake Victoria',
            'KENYA'                                  => 'Lake Naivasha',
            'NAIROBI'                                => 'Nairobi',
        ];

        $locationIds = [];
        foreach ($locationMap as $dataName => $destName) {
            if (isset($locationIds[$dataName])) continue;
            $dest = Destination::whereRaw('LOWER(name) = ?', [strtolower($destName)])->first();
            if (!$dest) {
                $country = in_array($destName, ['Nairobi', 'Masai Mara National Park', 'Lake Naivasha']) ? 'Kenya' : 'Tanzania';
                $dest = Destination::create([
                    'company_id' => $companyId,
                    'name'       => $destName,
                    'country'    => $country,
                ]);
            }
            $locationIds[$dataName] = $dest->id;
        }

        $records = [
            // ── LETTER L ──
            ['LA LUNA SUITE APARTMENTS', null, 'ARUSHA'],
            ['LAHA TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['LAKE BURUNGE BAOBAB TENTED LODGE', null, 'TARANGIRE NATIONAL PARK'],
            ['LAKE BURUNGE TENTED LODGE', null, 'TARANGIRE NATIONAL PARK'],
            ['LAKE DULUTI LODGE', null, 'ARUSHA'],
            ['LAKE EYASI SAFARI LODGE', 'BOUGAINVILLEA LODGES', 'LAKE EYASI'],
            ['LAKE MANYARA KILIMAMOJA LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            ['LAKE MANYARA SERENA SAFARI LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            ['LAKE MANYARA WILDLIFE LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            ['LAKE MASEK TENTED CAMP', 'TANGANYIKA WILDERNESS CAMPS', 'NDUTU'],
            ['LAKE NAIVASHA SAFARI LODGE', null, 'KENYA'],
            ['LAKE NATRON CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['LAKE NATRON TENTED CAMP', null, 'LAKE NATRON'],
            ['LE MERIDIEN ZANZIBAR', null, 'ZANZIBAR'],
            ['LEMARA ECO CAMP', null, 'MOROGORO'],
            ['LEONOTIS CAMP', null, 'LAKE NATRON'],
            ['LOBO WILDLIFE LODGE', null, 'LOBO'],
            // ── LETTER M ──
            ['MAA MAA LODGE', null, 'ARUSHA'],
            ['MAASAI ECO LODGE', null, 'KARATU'],
            ['MACHAME CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MALAIKA BEACH RESORT', null, 'MWANZA'],
            ['MALAIKA MARA RIVER LUXURY', 'MALAIKA CAMPS AND LODGES', 'SERENGETI NATIONAL PARK'],
            ['MALAIKA NDUTU LUXURY CAMP', 'MALAIKA CAMPS AND LODGES', 'NDUTU'],
            ['MALAIKA SERENGETI LUXURY CAMP', 'MALAIKA CAMPS AND LODGES', 'SERENGETI NATIONAL PARK'],
            ['MANDARA HUT', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MANYARA LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            ['MANYARA BEST VIEW LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            ['MANYARA BAOBAB LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            // skip duplicate "MANYARA BEST VIEW LODGE" - same slug as #27
            ['MANYARA FARM LODGE', null, 'LAKE MANYARA NATIONAL PARK'],
            ['MANYARA KILIMAMOJA', null, 'LAKE MANYARA NATIONAL PARK'],
            ['MARA HERITAGE MIGRATION CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['MARA KATI KATI TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['MARA MARA TENTED LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['MARA RIVER CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['MARA UNDER CANVAS CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['MARANGU TENTED LODGE', null, 'TARANGIRE NATIONAL PARK'],
            ['MANDARA GATE', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MARERA VALLEY LODGE', null, 'KARATU'],
            ['MARERA RETREAT', null, 'KARATU'],
            ['MASEK TENTED LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['MASEK TENTED CAMP', 'MAWE', 'SERENGETI NATIONAL PARK'],
            ['MOUNTAIN WONDERS LODGE', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MBWEHA TENT CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MELIA ARUSHA RESORT CAMP', null, 'ARUSHA'],
            ['MELIA ZANZIBAR', null, 'ZANZIBAR'],
            ['MELIA NGORONGORO LODGE', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['MELIA SERENGETI LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['MENO VIEW LODGE', null, 'ARUSHA'],
            ['MIGUNGA TENTED CAMP', null, 'LAKE MANYARA NATIONAL PARK'],
            ['MIKUMI WILDLIFE CAMP', null, 'MIKUMI NATIONAL PARK'],
            ['MIMAKARIBU', null, 'ARUSHA NATIONAL PARK'],
            ['MNONGWA BEACH COTTAGES', null, 'ZANZIBAR'],
            ['MNYAMA BEACH LODGE', null, 'ZANZIBAR'],
            ['MONT MERU HOTEL', null, 'ARUSHA'],
            ['MOUNT MERU GAME LODGE', null, 'ARUSHA'],
            ['MOSHI COFFEE LODGE', null, 'MOSHI'],
            ['MTO WA MBU CAMP', null, 'MTO WA MBU'],
            ['MTO WA MBU MIGRANT CAMP', null, 'KARATU'],
            ['MTO WA MBU HOTEL', null, 'ARUSHA'],
            ['MT MERU PEAK', null, 'ARUSHA NATIONAL PARK'],
            ['MT MERU VIEW CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MTONI VIEW LODGE', null, 'ARUSHA'],
            ['MVULI HOTEL ARUSHA', null, 'ARUSHA'],
            ['MVULI LODGE NGORONGORO', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['MVULI LODGE', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MVULI GATE', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['MY BLUE HOTEL', null, 'ZANZIBAR'],
            // ── LETTER N ──
            ['NABOISHO CAMP', 'ASILIA', 'Masai Mara Game Reserve'],
            ['NASIKIA MORU CAMP', 'NASIKIA CAMPS', 'SERENGETI NATIONAL PARK'],
            ['NASIKIA MOBILE MIGRATION SERENGETI', 'NASIKIA CAMPS', 'SERENGETI NATIONAL PARK'],
            ['NATRON RIVER CAMP', null, 'LAKE NATRON'],
            ['NDUTU BUSH CAMP', 'BUSH CAMPS', 'NDUTU'],
            ['NDUTU CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['NDUTU HERITAGE MIGRATION CAMP', null, 'NDUTU'],
            ['NDUTU KATI KATI TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['NDUTU PURE MIGRATION CAMP', null, 'NDUTU'],
            ['NDUTU SAFARI LODGE', null, 'NDUTU'],
            ['NDUTU UNDER CANVAS CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['NDUTU WILDERNESS CAMP', null, 'NDUTU'],
            ['NEPTUNE NGORONGORO LUXURY LODGE', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NEPTUNE PWANI BEACH RESORT', null, 'ZANZIBAR'],
            ['NEPTUNE SERENGETI LUXURY CAMP', 'NEPTUNE HOTELS', 'SERENGETI NATIONAL PARK'],
            ['NEW SAFARI HOTEL', null, 'ARUSHA'],
            ['NEW TEDDYS ON THE BEACH', null, 'ZANZIBAR'],
            ['NGARE LODGE', null, 'ARUSHA'],
            ['NGARE SERO MOUNTAIN LODGE', null, 'ARUSHA'],
            ['NGORONGORO COFFEE LODGE', 'BOUGAINVILLEA LODGES', 'KARATU'],
            ['NGORONGORO CRATER LODGE', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO FARM HOUSE', 'TANGANYIKA WILDERNESS CAMPS', 'KARATU'],
            ['NGORONGORO FARM HOUSE AND VALLEY', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO FARM HOUSE VALLEY', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO HARADALI HOME', 'HARADALI HOME', 'KARATU'],
            ['NGORONGORO LIONS PAW LUXURY CAMP', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO MARERA MOUNTAIN LODGE', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO OLDENI MOUNTAIN LODGE', null, 'KARATU'],
            ['NGORONGORO SAFARI LODGE', 'TANZANIA WILD CAMPS', 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO SAFARI RESORT', null, 'KARATU'],
            ['NGORONGORO SERENA SAFARI LODGE', 'SERENA HOTELS', 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO SOPA LODGE', 'SOPA LODGES', 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NGORONGORO WILD CAMP', null, 'NGORONGORO CONSERVATION AREA AUTHORITY'],
            ['NALUPANDA TENTED LODGE', null, 'KARATU'],
            ['NSYA LODGE AND CAMP', null, 'ARUSHA'],
            ['NUNGWI BEACH RESORT', null, 'ZANZIBAR'],
            ['NUR BEACH HOTEL', null, 'ZANZIBAR'],
            ['NYANGE FARM', null, 'MOSHI'],
            ['NYERERE TENTED CAMP', null, 'Nyerere National Park'],
            ['NYIKANI CENTRAL SERENGETI CAMP', 'NYIKANI CAMPS', 'SERENGETI NATIONAL PARK'],
            ['NYIKANI MIGRATION CAMP', 'NYIKANI CAMPS', 'SERENGETI NATIONAL PARK'],
            ['NYIKANI NDUTU CAMP', null, 'NDUTU'],
            ['NYIKANI TARANGIRE CAMP', 'NYIKANI CAMPS', 'TARANGIRE NATIONAL PARK'],
            ['NYOTA CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['NYUMBA NI RESIDENCE', null, 'ZANZIBAR'],
            ['NYUMBU LUXURY COLLECTION', null, 'SERENGETI NATIONAL PARK'],
            ['NYUMBU LUXURY MIGRATIONAL CAMP', null, 'NDUTU'],
            // ── LETTER O ──
            ['OCEAN PARADISE RESORT', null, 'ZANZIBAR'],
            ['OCTAGON LODGE', null, 'KARATU'],
            ['OLE SERAI LUXURY CAMP - MORU', null, 'SERENGETI NATIONAL PARK'],
            ['OLE SERAI LUXURY SERONERA', null, 'SERENGETI NATIONAL PARK'],
            ['OLEA FARM LODGE', null, 'KARATU'],
            ['OLERAI LODGE', null, 'ARUSHA'],
            ['OLESERAI LUXURY CAMP KOGATENDE', null, 'SERENGETI NATIONAL PARK'],
            ['OLESERAI LUXURY CAMPS - TURNER SPRING', null, 'SERENGETI NATIONAL PARK'],
            ['OLMARA CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['OLOIPONGON MAASAI CULTURAL VILLAGE', null, 'MOSHI'],
            ['OLSILIGILAI MAASAI LODGE', null, 'KILIMANJARO'],
            ['OSINON LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['OUTPOST LODGE', null, 'ARUSHA'],
            // ── LETTER P ──
            ['PAMOJA OLDE FARM LODGE', 'PAMOJA COLLECTION', 'KARATU'],
            ['PAMOJA SERENGETI LUXURY', 'PAMOJA COLLECTION', 'SERENGETI NATIONAL PARK'],
            ['PAMOJA TARANGIRE', null, 'TARANGIRE NATIONAL PARK'],
            ['PARADISE BEACH RESORT', 'PARADISE AND WILDERNESS', 'ZANZIBAR'],
            ['PAZURI INN', null, 'ARUSHA'],
            ['PEMBA PARADISE', null, 'PEMBA ISLAND'],
            ['PIKYA CAMP', null, 'LAKE NATRON'],
            ['PURE MIGRATION CAMP', null, 'SERENGETI NATIONAL PARK'],
            // ── LETTER R ──
            ['REEF AND BEACH RESORT', 'PARADISE AND WILDERNESS', 'ZANZIBAR'],
            ['RHOTIA VALLEY TENTED LODGE', null, 'KARATU'],
            ['RIVERTREES INN', null, 'USA RIVER ARUSHA'],
            ['ROIKA TARANGIRE TENTED LODGE', null, 'TARANGIRE NATIONAL PARK'],
            ['RONGAI 1 SEASONAL CAMPSITE', null, 'SERENGETI NATIONAL PARK'],
            // ── LETTER S ──
            ['SAADANI SAFARI LODGE', null, 'SAADANI NATIONAL PARK'],
            ['SADDLE HUT', null, 'ARUSHA NATIONAL PARK'],
            ['SAMAWA LIVING', null, 'ZANZIBAR'],
            ['SANGAWE TENTED LODGE', null, 'TARANGIRE NATIONAL PARK'],
            ['SANJAN CAMP', null, 'LAKE NATRON'],
            ['SCHOOL HUTS CAMP', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['SELOUS KINGA LODGE', null, 'SELOUS GAME RESERVE'],
            ['SELOUS MAPUMZIKO LODGE', null, 'SELOUS GAME RESERVE'],
            ['SELOUS RIVER CAMP', null, 'SELOUS GAME RESERVE'],
            ['SENSE OF WILDNESS', null, 'SERENGETI NATIONAL PARK'],
            ['SENTRIM MARA', null, 'MASAI MARA GAME RESERVE'],
            ['SERENA MIVUMO RIVER LODGE', 'SERENA HOTELS', 'SELOUS GAME RESERVE'],
            ['SERENGETI ACACIA BLISS', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI ARUSHA LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI CENTRAL LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI EXPLORER', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI HERITAGE TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI KATI KATI TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI KUHAMA CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI LAKE MAGADI LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI NORTH WILDERNESS CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI ORANG RIVER LODGE', 'THE ORANGI', 'SERENGETI NATIONAL PARK'],
            ['SERENGETI OSUPUKO CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI PURE CAMP', 'PURE TENTED', 'SERENGETI NATIONAL PARK'],
            ['SERENGETI QUEENS CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI RIVER CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SAFARI CAMP', 'NOMAD', 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SAFARI LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SAMETU TENTED LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SAVANNAH CAMP - MARA RIVER', 'SERENGETI SAVANNAH', 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SAVANNAH CAMP - NDUTU', 'SERENGETI SAVANNAH', 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SERENA SAFARI LODGE', 'SERENA HOTELS', 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SIMBA LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI SOPA LODGE', 'SOPA LODGES', 'SERENGETI NATIONAL PARK'],
            ['SERENGETI WILD CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI WILDEBEEST CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI WILDERNESS CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENGETI WOODLANDS CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SERENITY ON THE LAKE', null, 'LAKE VICTORIA'],
            ['SERONERA WILDLIFE LODGE', null, 'SERENGETI NATIONAL PARK'],
            ['SERVAAL WILDLIFE LODGE', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['SHABA BOUTIQUE HOTEL', 'PARADISE AND WILDERNESS', 'ZANZIBAR'],
            ['SHIRA 1', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['SHIRA 2', null, 'KILIMANJARO MOUNTAIN AREA'],
            ['SIGNATURE SERENGETI TENTED CAMP', null, 'SERENGETI NATIONAL PARK'],
            ['SIMBA FARM LODGE', null, 'KILIMANJARO NATIONAL PARK'],
            ['SOUND OF SILENCE', null, 'SERENGETI NATIONAL PARK'],
            ['SPICE ISLAND HOTEL AND RESORT', null, 'ZANZIBAR'],
            ['SULTAN SANDS ZANZIBAR', null, 'ZANZIBAR'],
            ['SUNNY PALMS RESORT', null, 'ZANZIBAR'],
            ['SUNSET KENDWA', null, 'ZANZIBAR'],
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
