<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationHolidaySupplement extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'supplement_date' => 'date',
        'supplement_amount' => 'decimal:2',
        'adult_rate' => 'decimal:2',
        'child_rate' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rateYear()
    {
        return $this->belongsTo(AccommodationRateYear::class, 'rate_year_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }
}
