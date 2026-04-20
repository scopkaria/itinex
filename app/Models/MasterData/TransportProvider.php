<?php

namespace App\Models\MasterData;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class TransportProvider extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'markup' => 'decimal:2'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicleTypes()
    {
        return $this->hasMany(VehicleType::class);
    }

    public function vehicles()
    {
        return $this->hasMany(ProviderVehicle::class);
    }

    public function drivers()
    {
        return $this->hasMany(TransportDriver::class);
    }

    public function transferRoutes()
    {
        return $this->hasMany(TransferRoute::class);
    }

    public function media()
    {
        return $this->hasMany(TransportMedia::class);
    }

    public function rates()
    {
        return $this->hasMany(TransportRate::class);
    }

    public function costSettings()
    {
        return $this->hasOne(TransportCostSetting::class);
    }

    public function documents()
    {
        return $this->hasMany(TransportDocument::class);
    }
}
