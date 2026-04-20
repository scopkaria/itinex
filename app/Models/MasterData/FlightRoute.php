<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class FlightRoute extends Model
{
    protected $guarded = ['id'];

    public function flightProvider()
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function originDestination()
    {
        return $this->belongsTo(Destination::class, 'origin_destination_id');
    }

    public function arrivalDestination()
    {
        return $this->belongsTo(Destination::class, 'arrival_destination_id');
    }
}
