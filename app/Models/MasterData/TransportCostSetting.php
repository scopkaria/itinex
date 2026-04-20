<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class TransportCostSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'fuel_cost_per_litre' => 'decimal:2',
        'driver_daily_rate' => 'decimal:2',
        'insurance_daily' => 'decimal:2',
        'maintenance_reserve' => 'decimal:2',
    ];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
