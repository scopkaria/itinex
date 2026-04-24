<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferRoute extends Model
{
    protected $guarded = ['id'];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function originDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'origin_destination_id');
    }

    public function arrivalDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'arrival_destination_id');
    }
}
