<?php

namespace App\Models\MasterData;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkFee extends Model
{
    protected $fillable = [
        'company_id',
        'park_name',
        'adult_price',
        'child_price',
        'resident_type',
    ];

    protected function casts(): array
    {
        return [
            'adult_price' => 'decimal:2',
            'child_price' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
