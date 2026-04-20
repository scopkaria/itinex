<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class ProviderVehicle extends Model
{
    protected $guarded = ['id'];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function driver()
    {
        return $this->belongsTo(TransportDriver::class, 'driver_id');
    }
}
