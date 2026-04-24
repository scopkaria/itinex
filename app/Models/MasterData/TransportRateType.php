<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportRateType extends Model
{
    protected $table = 'transport_rate_types';
    protected $guarded = ['id'];
    protected $casts = ['markup_percentage' => 'decimal:2', 'markup_fixed' => 'decimal:2'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
