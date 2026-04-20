<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class FlightChildPricing extends Model
{
    protected $table = 'flight_child_pricing';
    protected $guarded = ['id'];
    protected $casts = ['value' => 'decimal:2'];

    public function flightProvider()
    {
        return $this->belongsTo(FlightProvider::class);
    }
}
