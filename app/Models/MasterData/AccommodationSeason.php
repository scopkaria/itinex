<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationSeason extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function rateYear()
    {
        return $this->belongsTo(AccommodationRateYear::class, 'rate_year_id');
    }
}
