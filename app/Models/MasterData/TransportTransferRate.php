<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportTransferRate extends Model
{
    protected $table = 'transport_transfer_rates';
    protected $guarded = ['id'];
    protected $casts = [
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

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

    public function season(): BelongsTo
    {
        return $this->belongsTo(TransportSeason::class, 'transport_season_id');
    }
}
