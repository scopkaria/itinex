<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait TenantScoped
{
    protected function companyId(Request $request): int
    {
        return $request->user()->company_id;
    }
}
