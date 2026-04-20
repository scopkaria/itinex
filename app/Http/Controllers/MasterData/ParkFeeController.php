<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\ParkFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParkFeeController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $fees = ParkFee::where('company_id', $this->companyId($request))
            ->orderBy('park_name')
            ->get();

        return response()->json($fees);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'park_name' => ['required', 'string', 'max:255'],
            'adult_price' => ['required', 'numeric', 'min:0'],
            'child_price' => ['required', 'numeric', 'min:0'],
            'resident_type' => ['required', 'string', 'max:100'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $fee = ParkFee::create($data);

        return response()->json($fee, 201);
    }

    public function update(Request $request, ParkFee $parkFee): JsonResponse
    {
        $this->authorizeCompany($request, $parkFee);

        $data = $request->validate([
            'park_name' => ['sometimes', 'string', 'max:255'],
            'adult_price' => ['sometimes', 'numeric', 'min:0'],
            'child_price' => ['sometimes', 'numeric', 'min:0'],
            'resident_type' => ['sometimes', 'string', 'max:100'],
        ]);

        $parkFee->update($data);

        return response()->json($parkFee);
    }

    public function destroy(Request $request, ParkFee $parkFee): JsonResponse
    {
        $this->authorizeCompany($request, $parkFee);
        $parkFee->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function authorizeCompany(Request $request, ParkFee $parkFee): void
    {
        if ($parkFee->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
