<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationRateYear extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function seasons()
    {
        return $this->hasMany(AccommodationSeason::class, 'rate_year_id');
    }

    public function roomRates()
    {
        return $this->hasMany(AccommodationRoomRate::class, 'rate_year_id');
    }
}
