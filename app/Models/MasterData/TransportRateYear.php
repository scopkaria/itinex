<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRateYear extends Model
{
    protected $table = 'transport_rate_years';
    protected $guarded = ['id'];
    protected $casts = ['valid_from' => 'date', 'valid_to' => 'date'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(TransportSeason::class);
    }
}
