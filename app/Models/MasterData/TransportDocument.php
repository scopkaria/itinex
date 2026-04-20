<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class TransportDocument extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    public function transportProvider()
    {
        return $this->belongsTo(TransportProvider::class);
    }
}
