<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportCancellationPolicy extends Model
{
    protected $table = 'transport_cancellation_policies';
    protected $guarded = ['id'];
    protected $casts = ['penalty_percentage' => 'decimal:2'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(TransportSeason::class, 'transport_season_id');
    }
}
