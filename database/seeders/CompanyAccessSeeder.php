<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CompanyAccessSeeder extends Seeder
{
    public function run(): void
    {
        $tz = Country::where('code', 'TZ')->first();
        $ke = Country::where('code', 'KE')->first();
        $rw = Country::where('code', 'RW')->first();
        $ug = Country::where('code', 'UG')->first();

        // Safari Kings (company 1) => TZ, KE, RW, UG (East Africa)
        $c1 = Company::find(1);
        if ($c1 && $tz && $ke && $rw && $ug) {
            $c1->countries()->sync([$tz->id, $ke->id, $rw->id, $ug->id]);
            $this->command->info("Safari Kings: " . $c1->fresh()->countries->pluck('code')->join(', '));
        }

        // Zanzibar Dreams (company 2) => TZ only (Tanzania tier)
        $c2 = Company::find(2);
        if ($c2 && $tz) {
            $c2->countries()->sync([$tz->id]);
            $this->command->info("Zanzibar Dreams: " . $c2->fresh()->countries->pluck('code')->join(', '));
        }
    }
}
