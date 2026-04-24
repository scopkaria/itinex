<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationActivityModel extends Model
{
    protected $table = 'accommodation_activities';
    protected $guarded = ['id'];
    protected $casts = [
        'price_per_person' => 'decimal:2',
        'rate_adult' => 'decimal:2',
        'rate_child' => 'decimal:2',
        'rate_guide' => 'decimal:2',
        'rate_vehicle' => 'decimal:2',
        'rate_group' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
