<?php

namespace App\Models\Itinerary;

use App\Models\MasterData\Activity;
use App\Models\MasterData\Extra;
use App\Models\MasterData\Flight;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\DestinationFee;
use App\Models\MasterData\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    protected $fillable = [
        'itinerary_day_id',
        'type',
        'reference_id',
        'quantity',
        'cost',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(ItineraryDay::class, 'itinerary_day_id');
    }

    public function reference(): ?Model
    {
        return match ($this->type) {
            'hotel' => HotelRate::find($this->reference_id),
            'transport' => Vehicle::find($this->reference_id),
            'park_fee' => DestinationFee::find($this->reference_id),
            'activity' => Activity::find($this->reference_id),
            'extra' => Extra::find($this->reference_id),
            'flight' => Flight::find($this->reference_id),
            default => null,
        };
    }
}
