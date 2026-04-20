<?php

namespace App\Http\Controllers\Profit;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\Itinerary\Itinerary;
use App\Services\CostSheetService;
use App\Services\ProfitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfitController extends Controller
{
    use TenantScoped;

    public function __construct(
        private ProfitService $profitService,
        private CostSheetService $costSheetService,
    ) {}

    /**
     * GET /api/itineraries/{itinerary}/profit
     * Calculate and return P&L for an itinerary.
     */
    public function calculate(Request $request, int $id): JsonResponse
    {
        $itinerary = Itinerary::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $data = $this->profitService->getProfitAndLoss($itinerary);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POST /api/itineraries/{itinerary}/markup
     * Apply a markup percentage and recalculate selling price / profit / margin.
     */
    public function applyMarkup(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'markup_percentage' => 'required|numeric|min:0|max:500',
        ]);

        $itinerary = Itinerary::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $data = $this->profitService->applyMarkupToItinerary(
            $itinerary,
            (float) $request->markup_percentage
        );

        return response()->json([
            'success' => true,
            'message' => 'Markup applied successfully.',
            'data' => $data,
        ]);
    }

    /**
     * GET /api/itineraries/{itinerary}/pnl
     * Full P&L with cost sheet integration.
     */
    public function profitAndLoss(Request $request, int $id): JsonResponse
    {
        $itinerary = Itinerary::where('company_id', $this->companyId($request))
            ->findOrFail($id);

        $costSheet = $this->costSheetService->generate($itinerary);
        $pnl = $this->profitService->getProfitAndLoss($itinerary);

        return response()->json([
            'success' => true,
            'data' => [
                'profit_summary' => $pnl,
                'cost_breakdown' => [
                    'accommodation' => $costSheet['totals']['accommodation_total'],
                    'park_fees' => $costSheet['totals']['park_total'],
                    'transport' => $costSheet['totals']['transport_total'],
                    'flights' => $costSheet['totals']['flight_total'],
                    'extras' => $costSheet['totals']['extras_total'],
                ],
                'financials' => [
                    'total_cost' => $pnl['total_cost'],
                    'markup_percentage' => $pnl['markup_percentage'],
                    'selling_price' => $pnl['selling_price'],
                    'profit' => $pnl['profit'],
                    'margin_percentage' => $pnl['margin_percentage'],
                    'status' => $pnl['status'],
                    'per_person_cost' => $pnl['per_person_cost'],
                    'per_person_price' => $pnl['per_person_price'],
                ],
            ],
        ]);
    }
}
