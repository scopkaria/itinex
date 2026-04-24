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

    // ─── Pricing Engine Relations ─────────────────────────────
    public function rateYears()
    {
        return $this->hasMany(TransportRateYear::class);
    }

    public function seasons()
    {
        return $this->hasMany(TransportSeason::class);
    }

    public function rateTypes()
    {
        return $this->hasMany(TransportRateType::class);
    }

    public function transferRates()
    {
        return $this->hasMany(TransportTransferRate::class);
    }

    public function emptyRunRates()
    {
        return $this->hasMany(TransportEmptyRunRate::class);
    }

    public function vehicleDescriptions()
    {
        return $this->hasMany(TransportVehicleDescription::class);
    }

    public function impresetComponents()
    {
        return $this->hasMany(TransportImpresetComponent::class);
    }

    public function paymentPolicies()
    {
        return $this->hasMany(TransportPaymentPolicy::class);
    }

    public function cancellationPolicies()
    {
        return $this->hasMany(TransportCancellationPolicy::class);
    }

    public function rateVersions()
    {
        return $this->hasMany(TransportRateVersion::class);
    }
}
