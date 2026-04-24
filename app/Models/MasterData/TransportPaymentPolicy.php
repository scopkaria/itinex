<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportPaymentPolicy extends Model
{
    protected $table = 'transport_payment_policies';
    protected $guarded = ['id'];
    protected $casts = ['percentage_due' => 'decimal:2'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
