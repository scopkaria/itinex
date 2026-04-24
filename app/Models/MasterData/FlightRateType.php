<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightRateType extends Model
{
    protected $table = 'flight_rate_types';
    protected $guarded = ['id'];
    protected $casts = ['markup_percentage' => 'decimal:2', 'markup_fixed' => 'decimal:2'];

    public function flightProvider(): BelongsTo
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function scheduledFlights(): HasMany
    {
        return $this->hasMany(ScheduledFlight::class, 'flight_rate_type_id');
    }
}
