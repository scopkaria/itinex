<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ItineraryTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AfricanQueenSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['email' => 'info@africanqueenadventures.com'],
            [
                'name' => 'African Queen Adventures',
                'phone' => '+255 754 813 378',
                'address' => 'Arusha, Tanzania',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'info@africanqueenadventure.com'],
            [
                'company_id' => $company->id,
                'name' => 'Joseph Lyimo',
                'password' => Hash::make('Scopboy20'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        ItineraryTemplate::firstOrCreate(
            ['company_id' => $company->id, 'is_default' => true],
            [
                'name' => 'Default',
                'primary_color' => '#4f46e5',
                'font' => 'Helvetica',
                'layout_type' => 'classic',
                'footer_text' => $company->name . ' · ' . $company->email . ' · ' . $company->phone,
            ]
        );
    }
}
