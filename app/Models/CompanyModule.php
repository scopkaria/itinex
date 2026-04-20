<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyModule extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_enabled' => 'boolean'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
