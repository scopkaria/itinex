<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class TransferRoute extends Model
{
    protected $guarded = ['id'];

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
