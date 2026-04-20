<?php

namespace App\Http\Controllers\CostSheet;

use App\Http\Controllers\Controller;
use App\Models\Itinerary\Itinerary;
use App\Services\CostSheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CostSheetController extends Controller
{
    use \App\Http\Controllers\Traits\TenantScoped;

    public function __construct(
        private CostSheetService $costSheetService
    ) {}

    /**
     * Generate full cost sheet for an itinerary.
     */
    public function generate(Request $request, int $id): JsonResponse
    {
        $itinerary = Itinerary::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $costSheet = $this->costSheetService->generate($itinerary);

        return response()->json([
            'success' => true,
            'data' => $costSheet,
        ]);
    }

    /**
     * Get summary totals only.
     */
    public function summary(Request $request, int $id): JsonResponse
    {
        $itinerary = Itinerary::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $summary = $this->costSheetService->summary($itinerary);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get per-category totals.
     */
    public function categoryTotals(Request $request, int $id): JsonResponse
    {
        $itinerary = Itinerary::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $totals = $this->costSheetService->categoryTotals($itinerary);

        return response()->json([
            'success' => true,
            'data' => $totals,
        ]);
    }
}
