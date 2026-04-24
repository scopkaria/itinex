<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationPaymentPolicy extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'days_before' => 'integer',
        'percentage' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
