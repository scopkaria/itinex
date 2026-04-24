<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportSeason extends Model
{
    protected $table = 'transport_seasons';
    protected $guarded = ['id'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function rateYear(): BelongsTo
    {
        return $this->belongsTo(TransportRateYear::class, 'transport_rate_year_id');
    }

    public function transferRates(): HasMany
    {
        return $this->hasMany(TransportTransferRate::class, 'transport_season_id');
    }

    public function cancellationPolicies(): HasMany
    {
        return $this->hasMany(TransportCancellationPolicy::class, 'transport_season_id');
    }
}
