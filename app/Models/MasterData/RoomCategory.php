<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    protected $guarded = ['id'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
