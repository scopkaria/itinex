<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class AccommodationBackupRate extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'rate_data' => 'array',
        'snapshot_date' => 'date',
        'version_no' => 'integer',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function sourceRateYear()
    {
        return $this->belongsTo(AccommodationRateYear::class, 'source_rate_year_id');
    }
}
