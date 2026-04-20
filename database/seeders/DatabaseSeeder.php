<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ItineraryTemplate;
use App\Models\MasterData\MealPlan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Super Admin ────────────────────────────────────────
        User::factory()->create([
            'company_id' => null,
            'name' => 'Super Admin',
            'email' => 'admin@itinex.com',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        // ── Meal Plans ─────────────────────────────────────────
        foreach (['BB', 'HB', 'FB', 'AI'] as $plan) {
            MealPlan::firstOrCreate(['name' => $plan]);
        }

        // ── Company 1: Safari Kings ────────────────────────────
        $company1 = Company::create([
            'name' => 'Safari Kings Ltd',
            'email' => 'info@safarikings.com',
            'phone' => '+255 700 111 222',
            'address' => 'Arusha, Tanzania',
            'is_active' => true,
        ]);

        User::factory()->create([
            'company_id' => $company1->id,
            'name' => 'James Mwangi',
            'email' => 'james@safarikings.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        User::factory()->create([
            'company_id' => $company1->id,
            'name' => 'Grace Otieno',
            'email' => 'grace@safarikings.com',
            'password' => 'password',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        // ── Company 2: Zanzibar Dreams ─────────────────────────
        $company2 = Company::create([
            'name' => 'Zanzibar Dreams Tours',
            'email' => 'hello@zanzibardreams.com',
            'phone' => '+255 777 333 444',
            'address' => 'Stone Town, Zanzibar',
            'is_active' => true,
        ]);

        User::factory()->create([
            'company_id' => $company2->id,
            'name' => 'Amina Hassan',
            'email' => 'amina@zanzibardreams.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        User::factory()->create([
            'company_id' => $company2->id,
            'name' => 'Omar Said',
            'email' => 'omar@zanzibardreams.com',
            'password' => 'password',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        // ── Company 3: African Queen Adventures ────────────────
        $company3 = Company::create([
            'name' => 'African Queen Adventures',
            'email' => 'info@africanqueenadventures.com',
            'phone' => '+255 754 813 378',
            'address' => 'Arusha, Tanzania',
            'is_active' => true,
        ]);

        User::factory()->create([
            'company_id' => $company3->id,
            'name' => 'Joseph Lyimo',
            'email' => 'info@africanqueenadventure.com',
            'password' => 'Scopboy20',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // ── Default PDF Templates ──────────────────────────────
        foreach ([$company1, $company2, $company3] as $company) {
            ItineraryTemplate::firstOrCreate(
                ['company_id' => $company->id, 'is_default' => true],
                [
                    'name' => 'Default',
                    'primary_color' => '#4f46e5',
                    'font' => 'Helvetica',
                    'layout_type' => 'classic',
                    'footer_text' => $company->name . ' · ' . ($company->email ?? '') . ' · ' . ($company->phone ?? ''),
                ]
            );
        }
    }
}
