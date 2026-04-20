<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationChildPolicy extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['value' => 'decimal:2'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
