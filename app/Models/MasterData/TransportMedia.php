<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class TransportMedia extends Model
{
    protected $table = 'transport_media';
    protected $guarded = ['id'];
    protected $casts = ['is_cover' => 'boolean'];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
