<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightCharterRate extends Model
{
    protected $table = 'flight_charter_rates';
    protected $guarded = ['id'];
    protected $casts = ['price_per_hour' => 'decimal:2', 'min_price' => 'decimal:2'];

    public function flightProvider(): BelongsTo
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(FlightRoute::class, 'flight_route_id');
    }

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class, 'aircraft_type_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(FlightSeason::class, 'flight_season_id');
    }
}
