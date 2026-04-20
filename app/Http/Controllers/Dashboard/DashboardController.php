<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\Activity;
use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $companyId = $this->companyId($request);

        return response()->json([
            'destinations_count' => Destination::where('company_id', $companyId)->count(),
            'hotels_count' => Hotel::where('company_id', $companyId)->count(),
            'vehicles_count' => Vehicle::where('company_id', $companyId)->count(),
            'activities_count' => Activity::where('company_id', $companyId)->count(),
        ]);
    }
}
