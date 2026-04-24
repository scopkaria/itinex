<?php

namespace App\Services\Pricing;

use App\Models\MasterData\Hotel;
use App\Models\User;
use Illuminate\Support\Collection;

class RateVisibilityService
{
    public function sanitizeAccommodationRates(User $user, Hotel $hotel, Collection $rates): Collection
    {
        $isSuperAdmin = $user->isSuperAdmin();
        $isHotelOwner = $user->isHotel() && $hotel->owners()->where('users.id', $user->id)->exists();

        return $rates->map(function ($rate) use ($isSuperAdmin, $isHotelOwner) {
            $row = $rate->toArray();

            if (!$isSuperAdmin && !$isHotelOwner) {
                unset($row['sto_rate_raw']);
            }

            if ($isHotelOwner && !$isSuperAdmin) {
                unset($row['markup_percent']);
                unset($row['markup_fixed']);
                unset($row['derived_rate']);
                unset($row['visibility_mode']);
            }

            return $row;
        });
    }
}
