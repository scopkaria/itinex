<?php

namespace App\Models;

use App\Models\Itinerary\Itinerary;
use App\Models\MasterData\Activity;
use App\Models\MasterData\Destination;
use App\Models\MasterData\DestinationFee;
use App\Models\MasterData\Extra;
use App\Models\MasterData\Flight;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\ParkFee;
use App\Models\MasterData\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }

    public function parkFees(): HasMany
    {
        return $this->hasMany(ParkFee::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(Extra::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function destinationFees(): HasMany
    {
        return $this->hasMany(DestinationFee::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'company_country_access')->withTimestamps();
    }
}
