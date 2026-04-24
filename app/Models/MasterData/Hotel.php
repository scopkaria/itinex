<?php

namespace App\Models\MasterData;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'markup' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'location_id');
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(HotelRate::class);
    }

    public function roomCategories(): HasMany
    {
        return $this->hasMany(RoomCategory::class);
    }

    public function accommodationMedia(): HasMany
    {
        return $this->hasMany(AccommodationMedia::class);
    }

    public function rateYears(): HasMany
    {
        return $this->hasMany(AccommodationRateYear::class);
    }

    public function rateTypes(): HasMany
    {
        return $this->hasMany(AccommodationRateType::class);
    }

    public function roomRates(): HasMany
    {
        return $this->hasMany(AccommodationRoomRate::class);
    }

    public function extraFees(): HasMany
    {
        return $this->hasMany(AccommodationExtraFee::class);
    }

    public function holidaySupplements(): HasMany
    {
        return $this->hasMany(AccommodationHolidaySupplement::class);
    }

    public function accommodationActivities(): HasMany
    {
        return $this->hasMany(AccommodationActivityModel::class);
    }

    public function childPolicies(): HasMany
    {
        return $this->hasMany(AccommodationChildPolicy::class);
    }

    public function paymentPolicies(): HasMany
    {
        return $this->hasMany(AccommodationPaymentPolicy::class);
    }

    public function cancellationPolicies(): HasMany
    {
        return $this->hasMany(AccommodationCancellationPolicy::class);
    }

    public function tourLeaderDiscounts(): HasMany
    {
        return $this->hasMany(AccommodationTourLeaderDiscount::class);
    }

    public function backupRates(): HasMany
    {
        return $this->hasMany(AccommodationBackupRate::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'hotel_user_assignments');
    }
}
