<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class ScheduledFlight extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['operating_days' => 'array', 'is_active' => 'boolean'];

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
