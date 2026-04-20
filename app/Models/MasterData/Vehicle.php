<?php

namespace App\Models\MasterData;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'capacity',
        'price_per_day',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price_per_day' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
