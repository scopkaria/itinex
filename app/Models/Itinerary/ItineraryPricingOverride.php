<?php

namespace App\Models\Itinerary;

use Illuminate\Database\Eloquent\Model;

class ItineraryPricingOverride extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'override_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
