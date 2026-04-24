<?php

namespace App\Models\MasterData;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkFee extends Model
{
    protected $table = 'destination_fees';

    protected $fillable = [
        'company_id',
        'destination_id',
        'name',
        'supplier',
        'region',
        'fee_type',
        'season_id',
        'season_name',
        'valid_from',
        'valid_to',
        'nr_adult',
        'nr_child',
        'resident_adult',
        'resident_child',
        'citizen_adult',
        'citizen_child',
        'vehicle_rate',
        'guide_rate',
        'markup_type',
        'markup',
        'vat_type',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'nr_adult' => 'decimal:2',
            'nr_child' => 'decimal:2',
            'resident_adult' => 'decimal:2',
            'resident_child' => 'decimal:2',
            'citizen_adult' => 'decimal:2',
            'citizen_child' => 'decimal:2',
            'vehicle_rate' => 'decimal:2',
            'guide_rate' => 'decimal:2',
            'markup' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
