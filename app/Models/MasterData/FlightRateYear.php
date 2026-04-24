<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightRateYear extends Model
{
    protected $table = 'flight_rate_years';
    protected $guarded = ['id'];
    protected $casts = ['valid_from' => 'date', 'valid_to' => 'date'];

    public function flightProvider(): BelongsTo
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(FlightSeason::class);
    }

    public function childPricingRules(): HasMany
    {
        return $this->hasMany(FlightChildPricing::class);
    }

    public function scheduledFlights(): HasMany
    {
        return $this->hasMany(ScheduledFlight::class);
    }
}
