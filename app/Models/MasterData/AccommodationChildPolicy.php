<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationChildPolicy extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'value' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_fixed' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }

    public function season()
    {
        return $this->belongsTo(AccommodationSeason::class, 'season_id');
    }
}
