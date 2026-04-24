<?php

namespace App\Models\MasterData;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationRateVersion extends Model
{
    protected $table = 'accommodation_rate_versions';
    protected $guarded = ['id'];
    protected $casts = ['old_value' => 'decimal:2', 'new_value' => 'decimal:2'];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
