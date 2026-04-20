<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealPlan extends Model
{
    protected $fillable = [
        'name',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(HotelRate::class);
    }
}
