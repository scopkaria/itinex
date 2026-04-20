<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationRoomRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'adult_rate' => 'decimal:2',
        'child_rate' => 'decimal:2',
        'infant_rate' => 'decimal:2',
        'single_supplement' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rateYear()
    {
        return $this->belongsTo(AccommodationRateYear::class, 'rate_year_id');
    }

    public function season()
    {
        return $this->belongsTo(AccommodationSeason::class, 'season_id');
    }

    public function roomCategory()
    {
        return $this->belongsTo(RoomCategory::class, 'room_category_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }

    public function rateType()
    {
        return $this->belongsTo(AccommodationRateType::class, 'rate_type_id');
    }
}
