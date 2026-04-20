<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;

class CountryRegionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Tanzania', 'TZ', [
                'Northern Circuit', 'Southern Circuit', 'Western Circuit',
                'Zanzibar', 'Coastal', 'Southern Highlands', 'Lake Zone',
                'Southern Coast',
            ]],
            ['Kenya', 'KE', [
                'Maasai Mara Region', 'Amboseli Region', 'Nairobi',
                'Coast', 'Rift Valley', 'Laikipia', 'Northern Kenya',
                'Southern Kenya',
            ]],
            ['Uganda', 'UG', [
                'Kampala', 'Western Uganda', 'Bwindi', 'Murchison',
                'Queen Elizabeth', 'Kibale',
            ]],
            ['Rwanda', 'RW', [
                'Kigali', 'Volcanoes', 'Akagera', 'Nyungwe',
            ]],
            ['Burundi', 'BI', []],
            ['Botswana', 'BW', [
                'Okavango Delta', 'Chobe', 'Makgadikgadi', 'Central Kalahari',
            ]],
            ['Namibia', 'NA', [
                'Etosha', 'Sossusvlei', 'Damaraland', 'Caprivi Strip', 'Skeleton Coast',
            ]],
            ['South Africa', 'ZA', [
                'Kruger Region', 'Cape Town', 'Garden Route', 'KwaZulu-Natal',
            ]],
            ['Zimbabwe', 'ZW', [
                'Victoria Falls', 'Hwange', 'Mana Pools', 'Matobo',
            ]],
            ['Zambia', 'ZM', [
                'Livingstone', 'South Luangwa', 'Lower Zambezi', 'Kafue',
            ]],
            ['Ethiopia', 'ET', [
                'Addis Ababa', 'Simien Mountains', 'Omo Valley', 'Lalibela',
            ]],
            ['Egypt', 'EG', [
                'Cairo', 'Luxor', 'Aswan', 'Red Sea',
            ]],
            ['Morocco', 'MA', [
                'Marrakech', 'Fes', 'Sahara', 'Atlas Mountains',
            ]],
            ['Seychelles', 'SC', [
                'Mahé', 'Praslin', 'La Digue',
            ]],
            ['Madagascar', 'MG', [
                'Antananarivo', 'Andasibe', 'Isalo', 'Nosy Be',
            ]],
            ['Mozambique', 'MZ', [
                'Maputo', 'Bazaruto', 'Quirimbas', 'Gorongosa',
            ]],
            ['Democratic Republic of Congo', 'CD', [
                'Virunga',
            ]],
        ];

        foreach ($data as [$name, $code, $regions]) {
            $country = Country::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'continent' => 'Africa']
            );

            foreach ($regions as $regionName) {
                Region::firstOrCreate(
                    ['country_id' => $country->id, 'name' => $regionName]
                );
            }
        }

        $this->command->info('Seeded ' . Country::count() . ' countries and ' . Region::count() . ' regions.');
    }
}
