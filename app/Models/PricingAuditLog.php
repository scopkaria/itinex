<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingAuditLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];
}
