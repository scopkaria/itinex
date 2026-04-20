<?php

namespace App\Models\MasterData;

use App\Models\Company;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    protected $fillable = [
        'company_id',
        'country_id',
        'region_id',
        'name',
        'country',
        'region',
        'category',
        'supplier',
        'email',
        'description',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function countryRef(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function regionRef(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'location_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(DestinationFee::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(DestinationMedia::class)->orderBy('sort_order');
    }

    public function coverImage(): ?DestinationMedia
    {
        return $this->media()->where('is_cover', true)->first()
            ?? $this->media()->first();
    }
}
