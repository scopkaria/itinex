<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationCancellationPolicy extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['penalty_percentage' => 'decimal:2'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
