<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Destinations
            ['name' => 'destinations.view', 'group' => 'destinations', 'description' => 'View destinations'],
            ['name' => 'destinations.create', 'group' => 'destinations', 'description' => 'Create destinations'],
            ['name' => 'destinations.edit', 'group' => 'destinations', 'description' => 'Edit destinations'],
            ['name' => 'destinations.delete', 'group' => 'destinations', 'description' => 'Delete destinations'],

            // Accommodations
            ['name' => 'accommodations.view', 'group' => 'accommodations', 'description' => 'View accommodations'],
            ['name' => 'accommodations.create', 'group' => 'accommodations', 'description' => 'Create accommodations'],
            ['name' => 'accommodations.edit', 'group' => 'accommodations', 'description' => 'Edit accommodations'],
            ['name' => 'accommodations.delete', 'group' => 'accommodations', 'description' => 'Delete accommodations'],

            // Flights
            ['name' => 'flights.view', 'group' => 'flights', 'description' => 'View flight providers'],
            ['name' => 'flights.create', 'group' => 'flights', 'description' => 'Create flight providers'],
            ['name' => 'flights.edit', 'group' => 'flights', 'description' => 'Edit flight providers'],
            ['name' => 'flights.delete', 'group' => 'flights', 'description' => 'Delete flight providers'],

            // Transport
            ['name' => 'transport.view', 'group' => 'transport', 'description' => 'View transport providers'],
            ['name' => 'transport.create', 'group' => 'transport', 'description' => 'Create transport providers'],
            ['name' => 'transport.edit', 'group' => 'transport', 'description' => 'Edit transport providers'],
            ['name' => 'transport.delete', 'group' => 'transport', 'description' => 'Delete transport providers'],

            // Itineraries
            ['name' => 'itineraries.view', 'group' => 'itineraries', 'description' => 'View itineraries'],
            ['name' => 'itineraries.create', 'group' => 'itineraries', 'description' => 'Create itineraries'],
            ['name' => 'itineraries.edit', 'group' => 'itineraries', 'description' => 'Edit itineraries'],
            ['name' => 'itineraries.delete', 'group' => 'itineraries', 'description' => 'Delete itineraries'],

            // Activities
            ['name' => 'activities.view', 'group' => 'activities', 'description' => 'View activities'],
            ['name' => 'activities.create', 'group' => 'activities', 'description' => 'Create activities'],
            ['name' => 'activities.delete', 'group' => 'activities', 'description' => 'Delete activities'],

            // Extras
            ['name' => 'extras.view', 'group' => 'extras', 'description' => 'View extras'],
            ['name' => 'extras.create', 'group' => 'extras', 'description' => 'Create extras'],
            ['name' => 'extras.delete', 'group' => 'extras', 'description' => 'Delete extras'],

            // System
            ['name' => 'users.view', 'group' => 'system', 'description' => 'View users'],
            ['name' => 'users.manage', 'group' => 'system', 'description' => 'Manage users'],
            ['name' => 'companies.view', 'group' => 'system', 'description' => 'View companies'],
            ['name' => 'companies.manage', 'group' => 'system', 'description' => 'Manage companies'],
            ['name' => 'geography.manage', 'group' => 'system', 'description' => 'Manage geography'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }
    }
}
