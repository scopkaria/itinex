<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightSeason extends Model
{
    protected $table = 'flight_seasons';
    protected $guarded = ['id'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function flightProvider(): BelongsTo
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function rateYear(): BelongsTo
    {
        return $this->belongsTo(FlightRateYear::class, 'flight_rate_year_id');
    }

    public function scheduledFlights(): HasMany
    {
        return $this->hasMany(ScheduledFlight::class, 'flight_season_id');
    }

    public function charterRates(): HasMany
    {
        return $this->hasMany(FlightCharterRate::class, 'flight_season_id');
    }

    public function cancellationPolicies(): HasMany
    {
        return $this->hasMany(FlightCancellationPolicy::class, 'flight_season_id');
    }
}
