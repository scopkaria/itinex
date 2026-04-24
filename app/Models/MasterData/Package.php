<?php

namespace App\Models\MasterData;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'nights' => 'integer',
        'base_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'inclusions' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
