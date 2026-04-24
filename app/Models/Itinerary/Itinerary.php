<?php

namespace App\Models\Itinerary;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Itinerary extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'client_name',
        'number_of_people',
        'start_date',
        'end_date',
        'total_days',
        'total_cost',
        'total_price',
        'profit',
        'markup_percentage',
        'margin_percentage',
        'builder_state',
        'public_share_token',
    ];

    protected function casts(): array
    {
        return [
            'number_of_people' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'integer',
            'total_cost' => 'decimal:2',
            'total_price' => 'decimal:2',
            'profit' => 'decimal:2',
            'markup_percentage' => 'decimal:2',
            'margin_percentage' => 'decimal:2',
            'builder_state' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ItineraryDay::class)->orderBy('day_number');
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(ItineraryItem::class, ItineraryDay::class);
    }

    public function pricingOverrides(): HasMany
    {
        return $this->hasMany(ItineraryPricingOverride::class);
    }

    public function profitStatus(): string
    {
        $margin = (float) $this->margin_percentage;

        if ($margin > 20) {
            return 'profit';
        }

        if ($margin >= 5) {
            return 'low';
        }

        if ($margin < 0) {
            return 'loss';
        }

        return 'low';
    }
}
