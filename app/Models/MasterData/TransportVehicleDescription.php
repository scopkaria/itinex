<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportVehicleDescription extends Model
{
    protected $table = 'transport_vehicle_descriptions';
    protected $guarded = ['id'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }
}
