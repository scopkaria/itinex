<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportImpresetComponent extends Model
{
    protected $table = 'transport_imprest_components';
    protected $guarded = ['id'];
    protected $casts = ['cost' => 'decimal:2'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
