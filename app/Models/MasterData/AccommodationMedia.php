<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationMedia extends Model
{
    protected $table = 'accommodation_media';
    protected $guarded = ['id'];
    protected $casts = ['is_cover' => 'boolean'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
