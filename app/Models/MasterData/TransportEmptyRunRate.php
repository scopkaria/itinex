<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportEmptyRunRate extends Model
{
    protected $table = 'transport_empty_run_rates';
    protected $guarded = ['id'];
    protected $casts = ['rate' => 'decimal:2'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransferRoute::class, 'transfer_route_id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }
}
