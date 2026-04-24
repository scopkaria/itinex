<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationExtraFee extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'amount' => 'decimal:2',
        'adult_rate' => 'decimal:2',
        'child_rate' => 'decimal:2',
        'resident_rate' => 'decimal:2',
        'non_resident_rate' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rateYear()
    {
        return $this->belongsTo(AccommodationRateYear::class, 'rate_year_id');
    }
}
