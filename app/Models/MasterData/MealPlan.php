<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealPlan extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
        'full_name',
        'description_i18n',
    ];

    protected $casts = [
        'description_i18n' => 'array',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(AccommodationRoomRate::class);
    }
}
