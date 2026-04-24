<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AccommodationRoomRate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'sto_rate_raw' => 'encrypted',
        'adult_rate' => 'decimal:2',
        'contracted_rate' => 'decimal:2',
        'promotional_rate' => 'decimal:2',
        'derived_rate' => 'decimal:2',
        'markup_percent' => 'decimal:2',
        'markup_fixed' => 'decimal:2',
        'child_rate' => 'decimal:2',
        'infant_rate' => 'decimal:2',
        'single_supplement' => 'decimal:2',
        'per_person_sharing_double' => 'decimal:2',
        'per_person_sharing_twin' => 'decimal:2',
        'triple_adjustment' => 'decimal:2',
        'is_override' => 'boolean',
    ];

    public function scopeComputedVisibility(Builder $query): Builder
    {
        return $query->whereIn('visibility_mode', ['computed', 'computed_only']);
    }

    public function scopeCalculatorApproved(Builder $query): Builder
    {
        return $query
            ->computedVisibility()
            ->whereNotNull('derived_rate')
            ->where('derived_rate', '>', 0);
    }

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
