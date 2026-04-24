<?php

namespace App\Models\MasterData;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightRateVersion extends Model
{
    protected $table = 'flight_rate_versions';
    protected $guarded = ['id'];
    protected $casts = ['old_value' => 'decimal:2', 'new_value' => 'decimal:2'];

    public function flightProvider(): BelongsTo
    {
        return $this->belongsTo(FlightProvider::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
