<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationBackupRate extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['rate_data' => 'array'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
