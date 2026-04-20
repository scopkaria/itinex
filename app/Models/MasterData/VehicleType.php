<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $guarded = ['id'];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function vehicles()
    {
        return $this->hasMany(ProviderVehicle::class);
    }
}
