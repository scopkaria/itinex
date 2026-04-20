<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class TransportDriver extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['languages' => 'array'];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
