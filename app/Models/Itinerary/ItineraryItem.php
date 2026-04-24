<?php

namespace App\Models\Itinerary;

use App\Models\MasterData\Activity;
use App\Models\MasterData\Extra;
use App\Models\MasterData\Flight;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\Package;
use App\Models\MasterData\DestinationFee;
use App\Models\MasterData\ScheduledFlight;
use App\Models\MasterData\TransportTransferRate;
use App\Models\MasterData\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    protected $fillable = [
        'itinerary_day_id',
        'type',
        'reference_id',
        'reference_source',
        'quantity',
        'cost',
        'price',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(ItineraryDay::class, 'itinerary_day_id');
    }

    public function reference(): ?Model
    {
        if ($this->reference_source === 'scheduled_flight') {
            return ScheduledFlight::find($this->reference_id);
        }

        if ($this->reference_source === 'transport_transfer_rate') {
            return TransportTransferRate::with(['route.originDestination', 'route.arrivalDestination', 'vehicleType'])
                ->find($this->reference_id);
        }

        if ($this->reference_source === 'package') {
            return Package::find($this->reference_id);
        }

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
