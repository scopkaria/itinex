<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class FlightSeasonalRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'adult_rate' => 'decimal:2',
        'child_rate' => 'decimal:2',
        'infant_rate' => 'decimal:2',
        'charter_rate' => 'decimal:2',
    ];

    public function flightProvider()
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function route()
    {
        return $this->belongsTo(FlightRoute::class, 'flight_route_id');
    }

    public function aircraftType()
    {
        return $this->belongsTo(AircraftType::class);
    }
}
