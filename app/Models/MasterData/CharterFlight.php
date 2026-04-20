<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class CharterFlight extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['total_charter_price' => 'decimal:2'];

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
