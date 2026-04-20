<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationHolidaySupplement extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'supplement_amount' => 'decimal:2'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rateYear()
    {
        return $this->belongsTo(AccommodationRateYear::class, 'rate_year_id');
    }
}
