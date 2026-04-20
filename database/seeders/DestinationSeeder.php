<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MasterData\Destination;
use App\Models\MasterData\DestinationFee;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', 'LIKE', '%Safari Kings%')->first();

        if (! $company) {
            $this->command->error('Safari Kings company not found – run DatabaseSeeder first.');
            return;
        }

        $cid = $company->id;

        /*
        |----------------------------------------------------------------------
        | Destinations
        |----------------------------------------------------------------------
        | All parsed from legacy African Queen Adventures data.
        | Categories: national_park, conservancy, reserve, marine_park, other
        */

        $destinations = [
            // ── Tanzania National Parks ─────────────────────────
            ['name' => 'Serengeti National Park',       'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Ngorongoro Conservation Area',  'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'conservancy',   'supplier' => 'NCAA'],
            ['name' => 'Tarangire National Park',       'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Arusha National Park',          'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Lake Manyara National Park',    'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Kilimanjaro National Park',     'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Nyerere National Park',         'country' => 'Tanzania', 'region' => 'Southern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Ruaha National Park',           'country' => 'Tanzania', 'region' => 'Southern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Katavi National Park',          'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Gombe National Park',           'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Mahale Mountains National Park','country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Mikumi National Park',          'country' => 'Tanzania', 'region' => 'Southern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Saadani National Park',         'country' => 'Tanzania', 'region' => 'Coastal',            'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Kitulo National Park',          'country' => 'Tanzania', 'region' => 'Southern Highlands', 'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Mkomazi National Park',         'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Udzungwa Mountains National Park','country'=>'Tanzania', 'region' => 'Southern Circuit',   'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Rubondo Island National Park',  'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Burigi-Chato National Park',    'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Ibanda-Kyerwa National Park',   'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Rumanyika-Karagwe National Park','country'=>'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Saanane Island National Park',  'country' => 'Tanzania', 'region' => 'Lake Zone',          'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Kigosi National Park',          'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],
            ['name' => 'Ugalla River National Park',    'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'national_park', 'supplier' => 'TANAPA'],

            // ── Tanzania Attractions (not NP) ──────────────────
            ['name' => 'Lake Natron',                   'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'other',         'supplier' => null],
            ['name' => 'Ol Duvai Gorge',                'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'other',         'supplier' => 'NCAA'],
            ['name' => 'Mount Kilimanjaro',             'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'other',         'supplier' => 'TANAPA'],
            ['name' => 'Empakaai Crater',               'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'other',         'supplier' => 'NCAA'],

            // ── Tanzania Conservancies / WMAs ──────────────────
            ['name' => 'Chem Chem Conservancy',         'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'conservancy',   'supplier' => 'Chem Chem'],
            ['name' => 'Mwiba Wildlife Reserve',        'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'reserve',       'supplier' => 'Mwiba'],
            ['name' => 'Singita Kiwoito Reserve',       'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'reserve',       'supplier' => 'Singita'],
            ['name' => 'Asilia Conservancy',            'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'conservancy',   'supplier' => 'Asilia'],
            ['name' => 'Olkeri WMA',                    'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'reserve',       'supplier' => null],
            ['name' => 'Manyara Ranch Conservancy',     'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'conservancy',   'supplier' => null],
            ['name' => 'Randilen WMA',                  'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'reserve',       'supplier' => null],
            ['name' => 'Enduimet WMA',                  'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'reserve',       'supplier' => null],
            ['name' => 'Ikona WMA',                     'country' => 'Tanzania', 'region' => 'Northern Circuit',   'category' => 'reserve',       'supplier' => null],
            ['name' => 'Makao WMA',                     'country' => 'Tanzania', 'region' => 'Western Circuit',    'category' => 'reserve',       'supplier' => null],

            // ── Zanzibar / Islands ─────────────────────────────
            ['name' => 'Mnemba Island',                 'country' => 'Tanzania', 'region' => 'Zanzibar',           'category' => 'marine_park',   'supplier' => null],
            ['name' => 'Fanjove Island',                'country' => 'Tanzania', 'region' => 'Southern Coast',     'category' => 'marine_park',   'supplier' => null],
            ['name' => 'Mafia Island Marine Park',      'country' => 'Tanzania', 'region' => 'Southern Coast',     'category' => 'marine_park',   'supplier' => 'TANAPA'],

            // ── Kenya ──────────────────────────────────────────
            ['name' => 'Amboseli National Park',        'country' => 'Kenya',    'region' => 'Southern Kenya',     'category' => 'national_park', 'supplier' => 'KWS'],
            ['name' => 'Masai Mara National Reserve',   'country' => 'Kenya',    'region' => 'Rift Valley',        'category' => 'reserve',       'supplier' => 'Narok County'],
            ['name' => 'Nairobi National Park',         'country' => 'Kenya',    'region' => 'Nairobi',            'category' => 'national_park', 'supplier' => 'KWS'],
            ['name' => 'Loisaba Conservancy',           'country' => 'Kenya',    'region' => 'Laikipia',           'category' => 'conservancy',   'supplier' => 'Loisaba'],
            ['name' => 'Mara Naibosho Conservancy',     'country' => 'Kenya',    'region' => 'Rift Valley',        'category' => 'conservancy',   'supplier' => null],
            ['name' => 'Mara North Conservancy',        'country' => 'Kenya',    'region' => 'Rift Valley',        'category' => 'conservancy',   'supplier' => null],
            ['name' => 'Tsavo East National Park',      'country' => 'Kenya',    'region' => 'Coast',              'category' => 'national_park', 'supplier' => 'KWS'],
            ['name' => 'Tsavo West National Park',      'country' => 'Kenya',    'region' => 'Coast',              'category' => 'national_park', 'supplier' => 'KWS'],
            ['name' => 'Lake Nakuru National Park',     'country' => 'Kenya',    'region' => 'Rift Valley',        'category' => 'national_park', 'supplier' => 'KWS'],
            ['name' => 'Samburu National Reserve',      'country' => 'Kenya',    'region' => 'Northern Kenya',     'category' => 'reserve',       'supplier' => 'County'],

            // ── Rwanda ─────────────────────────────────────────
            ['name' => 'Volcanoes National Park',       'country' => 'Rwanda',   'region' => null,                 'category' => 'national_park', 'supplier' => 'RDB'],
            ['name' => 'Akagera National Park',         'country' => 'Rwanda',   'region' => null,                 'category' => 'national_park', 'supplier' => 'RDB'],
            ['name' => 'Nyungwe Forest National Park',  'country' => 'Rwanda',   'region' => null,                 'category' => 'national_park', 'supplier' => 'RDB'],

            // ── Uganda ─────────────────────────────────────────
            ['name' => 'Bwindi Impenetrable National Park','country'=>'Uganda',  'region' => null,                 'category' => 'national_park', 'supplier' => 'UWA'],
            ['name' => 'Queen Elizabeth National Park', 'country' => 'Uganda',   'region' => null,                 'category' => 'national_park', 'supplier' => 'UWA'],
            ['name' => 'Murchison Falls National Park', 'country' => 'Uganda',   'region' => null,                 'category' => 'national_park', 'supplier' => 'UWA'],
            ['name' => 'Kibale Forest National Park',   'country' => 'Uganda',   'region' => null,                 'category' => 'national_park', 'supplier' => 'UWA'],
        ];

        $destMap = [];

        foreach ($destinations as $row) {
            $d = Destination::firstOrCreate(
                ['company_id' => $cid, 'name' => $row['name']],
                array_merge($row, ['company_id' => $cid])
            );
            $destMap[$row['name']] = $d->id;
        }

        $this->command->info('Created ' . count($destMap) . ' destinations.');

        /*
        |----------------------------------------------------------------------
        | Destination Fees — parsed from legacy data
        |----------------------------------------------------------------------
        | Structure: [destination_name, fee_type, season_name, valid_from, valid_to,
        |             nr_adult, nr_child, resident_adult, resident_child,
        |             citizen_adult, citizen_child, vehicle_rate, guide_rate,
        |             vat_type]
        |
        | Prices from legacy data where available, 0 where admin should fill.
        */

        $fees = [
            // ── Serengeti National Park ────────────────────────
            ['Serengeti National Park', 'Park Fee',          'July - June', '2024-07-01', '2025-06-30', 82.60, 23.60, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Serengeti National Park', 'Concession Fee',    'High Season', '2024-06-01', '2024-10-31', 60.00, 20.00, 60.00, 20.00, 0, 0, 0, 0, 'inclusive'],
            ['Serengeti National Park', 'Concession Fee',    'Low Season',  '2024-11-01', '2025-05-31', 40.00, 15.00, 40.00, 15.00, 0, 0, 0, 0, 'inclusive'],
            ['Serengeti National Park', 'Wildlife Fee',      'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Ngorongoro Conservation Area ───────────────────
            ['Ngorongoro Conservation Area', 'Conservation Fee', 'July - June', '2024-07-01', '2025-06-30', 82.60, 23.60, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Ngorongoro Conservation Area', 'Crater Service Fee','July - June','2024-07-01','2025-06-30', 295.00, 295.00, 17.70, 17.70, 0, 0, 236.00, 0, 'inclusive'],
            ['Ngorongoro Conservation Area', 'Transit Fee',   'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Tarangire National Park ────────────────────────
            ['Tarangire National Park', 'Park Fee',          'July - June', '2024-07-01', '2025-06-30', 53.10, 17.70, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Tarangire National Park', 'Night Park Fee',    'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Tarangire National Park', 'Walking Fee',       'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Arusha National Park ───────────────────────────
            ['Arusha National Park', 'Park Fee',             'July - June', '2024-07-01', '2025-06-30', 53.10, 17.70, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Arusha National Park', 'Conservation Fee',     'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Arusha National Park', 'Canoe Fee',            'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Arusha National Park', 'Walking Fee',          'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Lake Manyara National Park ─────────────────────
            ['Lake Manyara National Park', 'Park Fee',       'July - June', '2024-07-01', '2025-06-30', 53.10, 17.70, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Lake Manyara National Park', 'Night Park Fee', 'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Lake Manyara National Park', 'Walking Fee',    'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Lake Manyara National Park', 'Canoe Fee',      'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Kilimanjaro National Park ──────────────────────
            ['Kilimanjaro National Park', 'Park Fee',        'July - June', '2024-07-01', '2025-06-30', 82.60, 23.60, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Nyerere National Park ──────────────────────────
            ['Nyerere National Park', 'Park Fee',            'High Season', '2024-06-01', '2024-10-31', 53.10, 17.70, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Nyerere National Park', 'Park Fee',            'Low Season',  '2024-11-01', '2025-05-31', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Nyerere National Park', 'Wildlife Fee',        'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Ruaha National Park ────────────────────────────
            ['Ruaha National Park', 'Park Fee',              'High Season', '2024-06-01', '2024-10-31', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Ruaha National Park', 'Park Fee',              'Low Season',  '2024-11-01', '2025-05-31', 23.60, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Ruaha National Park', 'Walking Fee',           'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Katavi National Park ───────────────────────────
            ['Katavi National Park', 'Park Fee',             'High Season', '2024-06-01', '2024-10-31', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],
            ['Katavi National Park', 'Park Fee',             'Low Season',  '2024-11-01', '2025-05-31', 23.60, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Gombe National Park ────────────────────────────
            ['Gombe National Park', 'Park Fee',              'High Season', '2024-06-01', '2024-10-31', 118.00, 35.40, 29.50, 8.85, 5900, 1180, 0, 0, 'inclusive'],
            ['Gombe National Park', 'Park Fee',              'Low Season',  '2024-11-01', '2025-05-31', 82.60, 23.60, 29.50, 8.85, 5900, 1180, 0, 0, 'inclusive'],

            // ── Mahale Mountains National Park ─────────────────
            ['Mahale Mountains National Park', 'Park Fee',   'High Season', '2024-06-01', '2024-10-31', 94.40, 35.40, 29.50, 8.85, 5900, 1180, 0, 0, 'inclusive'],
            ['Mahale Mountains National Park', 'Park Fee',   'Low Season',  '2024-11-01', '2025-05-31', 59.00, 23.60, 29.50, 8.85, 5900, 1180, 0, 0, 'inclusive'],

            // ── Mikumi National Park ───────────────────────────
            ['Mikumi National Park', 'Park Fee',             'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Saadani National Park ──────────────────────────
            ['Saadani National Park', 'Park Fee',            'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Kitulo National Park ───────────────────────────
            ['Kitulo National Park', 'Park Fee',             'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Mkomazi National Park ──────────────────────────
            ['Mkomazi National Park', 'Park Fee',            'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Udzungwa Mountains National Park ───────────────
            ['Udzungwa Mountains National Park', 'Park Fee', 'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Rubondo Island National Park ───────────────────
            ['Rubondo Island National Park', 'Park Fee',     'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Burigi-Chato National Park ─────────────────────
            ['Burigi-Chato National Park', 'Park Fee',       'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Ibanda-Kyerwa National Park ────────────────────
            ['Ibanda-Kyerwa National Park', 'Park Fee',      'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Rumanyika-Karagwe National Park ────────────────
            ['Rumanyika-Karagwe National Park', 'Park Fee',  'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Saanane Island National Park ───────────────────
            ['Saanane Island National Park', 'Park Fee',     'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Kigosi National Park ───────────────────────────
            ['Kigosi National Park', 'Park Fee',             'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Ugalla River National Park ─────────────────────
            ['Ugalla River National Park', 'Park Fee',       'Year Round',  '2024-07-01', '2025-06-30', 35.40, 11.80, 17.70, 5.90, 5900, 1180, 0, 0, 'inclusive'],

            // ── Lake Natron ────────────────────────────────────
            ['Lake Natron', 'Park Fee',                      'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Ol Duvai Gorge ─────────────────────────────────
            ['Ol Duvai Gorge', 'Conservation Fee',           'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Mount Kilimanjaro ──────────────────────────────
            ['Mount Kilimanjaro', 'Permit',                  'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Empakaai Crater ────────────────────────────────
            ['Empakaai Crater', 'Conservation Fee',          'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Empakaai Crater', 'Ranger Fee',                'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Chem Chem Conservancy ──────────────────────────
            ['Chem Chem Conservancy', 'Conservancy Fee',     'High Season', '2024-06-01', '2024-10-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Chem Chem Conservancy', 'Conservancy Fee',     'Low Season',  '2024-11-01', '2025-05-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Mwiba Wildlife Reserve ─────────────────────────
            ['Mwiba Wildlife Reserve', 'Concession Fee',     'High Season', '2024-06-01', '2024-10-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Mwiba Wildlife Reserve', 'Concession Fee',     'Low Season',  '2024-11-01', '2025-05-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Singita Kiwoito Reserve ────────────────────────
            ['Singita Kiwoito Reserve', 'Concession Fee',    'High Season', '2024-06-01', '2024-10-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Singita Kiwoito Reserve', 'Concession Fee',    'Low Season',  '2024-11-01', '2025-05-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Asilia Conservancy ─────────────────────────────
            ['Asilia Conservancy', 'Conservancy Fee',        'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Olkeri WMA ─────────────────────────────────────
            ['Olkeri WMA', 'WMA Fee',                        'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Manyara Ranch Conservancy ──────────────────────
            ['Manyara Ranch Conservancy', 'Conservancy Fee', 'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Manyara Ranch Conservancy', 'Walking Fee',     'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Manyara Ranch Conservancy', 'Village Fee',     'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Randilen WMA ───────────────────────────────────
            ['Randilen WMA', 'WMA Fee',                      'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Enduimet WMA ───────────────────────────────────
            ['Enduimet WMA', 'WMA Fee',                      'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Ikona WMA ──────────────────────────────────────
            ['Ikona WMA', 'WMA Fee',                         'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Makao WMA ──────────────────────────────────────
            ['Makao WMA', 'WMA Fee',                         'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Mnemba Island ──────────────────────────────────
            ['Mnemba Island', 'Park Fee',                    'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Fanjove Island ─────────────────────────────────
            ['Fanjove Island', 'Concession Fee',             'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Mafia Island Marine Park ───────────────────────
            ['Mafia Island Marine Park', 'Park Fee',         'Year Round',  '2024-07-01', '2025-06-30', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Amboseli National Park (Kenya) ─────────────────
            ['Amboseli National Park', 'Park Fee',           'Year Round',  '2024-01-01', '2024-12-31', 60.00, 35.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Masai Mara National Reserve (Kenya) ────────────
            ['Masai Mara National Reserve', 'Park Fee',      'High Season', '2024-07-01', '2024-10-31', 200.00, 100.00, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Masai Mara National Reserve', 'Park Fee',      'Low Season',  '2024-11-01', '2025-06-30', 100.00, 50.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Nairobi National Park (Kenya) ──────────────────
            ['Nairobi National Park', 'Park Fee',            'Year Round',  '2024-01-01', '2024-12-31', 43.00, 22.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Loisaba Conservancy (Kenya) ────────────────────
            ['Loisaba Conservancy', 'Conservancy Fee',       'Year Round',  '2024-01-01', '2024-12-31', 110.00, 55.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Mara Naibosho Conservancy (Kenya) ──────────────
            ['Mara Naibosho Conservancy', 'Conservancy Fee', 'Year Round',  '2024-01-01', '2024-12-31', 120.00, 60.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Mara North Conservancy (Kenya) ─────────────────
            ['Mara North Conservancy', 'Conservancy Fee',    'Year Round',  '2024-01-01', '2024-12-31', 120.00, 60.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Tsavo East National Park (Kenya) ───────────────
            ['Tsavo East National Park', 'Park Fee',         'Year Round',  '2024-01-01', '2024-12-31', 52.00, 26.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Tsavo West National Park (Kenya) ───────────────
            ['Tsavo West National Park', 'Park Fee',         'Year Round',  '2024-01-01', '2024-12-31', 52.00, 26.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Lake Nakuru National Park (Kenya) ──────────────
            ['Lake Nakuru National Park', 'Park Fee',        'Year Round',  '2024-01-01', '2024-12-31', 60.00, 35.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Samburu National Reserve (Kenya) ───────────────
            ['Samburu National Reserve', 'Park Fee',         'Year Round',  '2024-01-01', '2024-12-31', 70.00, 40.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Volcanoes National Park (Rwanda) ───────────────
            ['Volcanoes National Park', 'Park Fee',          'Year Round',  '2024-01-01', '2024-12-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Volcanoes National Park', 'Permit',            'Year Round',  '2024-01-01', '2024-12-31', 1500.00, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Akagera National Park (Rwanda) ─────────────────
            ['Akagera National Park', 'Park Fee',            'Year Round',  '2024-01-01', '2024-12-31', 50.00, 25.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Nyungwe Forest National Park (Rwanda) ──────────
            ['Nyungwe Forest National Park', 'Park Fee',     'Year Round',  '2024-01-01', '2024-12-31', 50.00, 25.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Bwindi Impenetrable National Park (Uganda) ─────
            ['Bwindi Impenetrable National Park', 'Park Fee','Year Round',  '2024-01-01', '2024-12-31', 0, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Bwindi Impenetrable National Park', 'Permit',  'Year Round',  '2024-01-01', '2024-12-31', 800.00, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Queen Elizabeth National Park (Uganda) ─────────
            ['Queen Elizabeth National Park', 'Park Fee',    'Year Round',  '2024-01-01', '2024-12-31', 40.00, 20.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Murchison Falls National Park (Uganda) ─────────
            ['Murchison Falls National Park', 'Park Fee',    'Year Round',  '2024-01-01', '2024-12-31', 40.00, 20.00, 0, 0, 0, 0, 0, 0, 'inclusive'],

            // ── Kibale Forest National Park (Uganda) ───────────
            ['Kibale Forest National Park', 'Park Fee',      'Year Round',  '2024-01-01', '2024-12-31', 50.00, 25.00, 0, 0, 0, 0, 0, 0, 'inclusive'],
            ['Kibale Forest National Park', 'Permit',        'Year Round',  '2024-01-01', '2024-12-31', 250.00, 0, 0, 0, 0, 0, 0, 0, 'inclusive'],
        ];

        $count = 0;

        foreach ($fees as $f) {
            $destId = $destMap[$f[0]] ?? null;
            if (! $destId) {
                $this->command->warn("Skipped fee: destination '{$f[0]}' not found.");
                continue;
            }

            DestinationFee::firstOrCreate(
                [
                    'company_id'      => $cid,
                    'destination_id'  => $destId,
                    'fee_type'        => $f[1],
                    'season_name'     => $f[2],
                    'valid_from'      => $f[3],
                    'valid_to'        => $f[4],
                ],
                [
                    'nr_adult'        => $f[5],
                    'nr_child'        => $f[6],
                    'resident_adult'  => $f[7],
                    'resident_child'  => $f[8],
                    'citizen_adult'   => $f[9],
                    'citizen_child'   => $f[10],
                    'vehicle_rate'    => $f[11],
                    'guide_rate'      => $f[12],
                    'vat_type'        => $f[13],
                    'markup'          => 0,
                ]
            );

            $count++;
        }

        $this->command->info("Seeded {$count} destination fees.");
    }
}
