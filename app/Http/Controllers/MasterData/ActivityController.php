<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $activities = Activity::where('company_id', $this->companyId($request))
            ->orderBy('name')
            ->get();

        return response()->json($activities);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_per_person' => ['required', 'numeric', 'min:0'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $activity = Activity::create($data);

        return response()->json($activity, 201);
    }

    public function update(Request $request, Activity $activity): JsonResponse
    {
        $this->authorizeCompany($request, $activity);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'price_per_person' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $activity->update($data);

        return response()->json($activity);
    }

    public function destroy(Request $request, Activity $activity): JsonResponse
    {
        $this->authorizeCompany($request, $activity);
        $activity->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function authorizeCompany(Request $request, Activity $activity): void
    {
        if ($activity->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
