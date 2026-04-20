<?php

namespace App\Models\MasterData;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class FlightProvider extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'markup' => 'decimal:2'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function aircraftTypes()
    {
        return $this->hasMany(AircraftType::class);
    }

    public function routes()
    {
        return $this->hasMany(FlightRoute::class);
    }

    public function seasonalRates()
    {
        return $this->hasMany(FlightSeasonalRate::class);
    }

    public function scheduledFlights()
    {
        return $this->hasMany(ScheduledFlight::class);
    }

    public function charterFlights()
    {
        return $this->hasMany(CharterFlight::class);
    }

    public function childPricing()
    {
        return $this->hasMany(FlightChildPricing::class);
    }

    public function policies()
    {
        return $this->hasMany(FlightPolicy::class);
    }
}
