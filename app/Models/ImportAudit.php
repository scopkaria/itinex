<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ImportAudit extends Model
{
    protected $table = 'import_audit';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'violations' => 'array',
            'raw_row'    => 'array',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'accepted');
    }

    public function scopeSkipped(Builder $query): Builder
    {
        return $query->where('status', 'skipped');
    }

    public function scopeForSource(Builder $query, string $source): Builder
    {
        return $query->where('source_table', $source);
    }
}
