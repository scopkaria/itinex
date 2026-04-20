<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class TransportRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rate' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function transferRoute()
    {
        return $this->belongsTo(TransferRoute::class);
    }
}
