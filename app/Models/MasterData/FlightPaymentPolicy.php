<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightPaymentPolicy extends Model
{
    protected $table = 'flight_payment_policies';
    protected $guarded = ['id'];
    protected $casts = ['percentage_due' => 'decimal:2'];

    public function flightProvider(): BelongsTo
    {
        return $this->belongsTo(FlightProvider::class);
    }
}
