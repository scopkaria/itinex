<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class FlightPolicy extends Model
{
    protected $guarded = ['id'];

    public function flightProvider()
    {
        return $this->belongsTo(FlightProvider::class);
    }
}
