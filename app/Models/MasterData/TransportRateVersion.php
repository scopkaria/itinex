<?php

namespace App\Models\MasterData;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportRateVersion extends Model
{
    protected $table = 'transport_rate_versions';
    protected $guarded = ['id'];
    protected $casts = ['old_value' => 'decimal:2', 'new_value' => 'decimal:2'];

    public function transportProvider(): BelongsTo
    {
        return $this->belongsTo(TransportProvider::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
