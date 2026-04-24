<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightCancellationPolicy extends Model
{
    protected $table = 'flight_cancellation_policies';
    protected $guarded = ['id'];
    protected $casts = ['penalty_percentage' => 'decimal:2'];

    public function flightProvider(): BelongsTo
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(FlightSeason::class, 'flight_season_id');
    }
}
