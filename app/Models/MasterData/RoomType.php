<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'hotel_id',
        'type',
        'label',
        'max_adults',
    ];

    protected $casts = [
        'max_adults' => 'integer',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(AccommodationRoomRate::class);
    }
}
