<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\TransportProvider;
use App\Models\MasterData\TransportRateYear;
use App\Models\MasterData\TransportSeason;
use App\Models\MasterData\TransportRateType;
use App\Models\MasterData\TransportTransferRate;
use App\Models\MasterData\TransportPaymentPolicy;
use App\Models\MasterData\TransportCancellationPolicy;
use App\Models\MasterData\TransferRoute;
use App\Models\MasterData\VehicleType;
use App\Services\Pricing\RateAuditVersioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransportPricingEngineController extends Controller
{
    use TenantScoped;

    public function __construct(
        private readonly RateAuditVersioningService $rateAuditService,
    ) {
    }

    // ─── YEARS ────────────────────────────────────────────────
    public function indexYears(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $years = $provider->rateYears()
            ->with('seasons')
            ->orderBy('year', 'desc')
            ->get()
            ->map(fn($y) => [
                'id' => $y->id,
                'year' => $y->year,
                'valid_from' => $y->valid_from->format('M d, Y'),
                'valid_to' => $y->valid_to->format('M d, Y'),
                'status' => $y->status,
                'season_count' => $y->seasons->count(),
            ]);

        return response()->json($years);
    }

    public function storeYear(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after:valid_from'],
            'status' => ['nullable', 'in:draft,active,archived'],
        ]);

        $data['transport_provider_id'] = $provider->id;
        $data['status'] = $data['status'] ?? 'draft';

        $year = TransportRateYear::create($data);

        return response()->json([
            'id' => $year->id,
            'year' => $year->year,
            'message' => 'Year created successfully.',
        ], 201);
    }

    public function updateYear(Request $request, TransportProvider $provider, TransportRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'valid_from' => ['sometimes', 'date'],
            'valid_to' => ['sometimes', 'date', 'after:valid_from'],
            'status' => ['sometimes', 'in:draft,active,archived'],
        ]);

        $year->update($data);

        return response()->json(['message' => 'Year updated successfully.']);
    }

    public function destroyYear(Request $request, TransportProvider $provider, TransportRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $year->delete();

        return response()->json(['message' => 'Year deleted successfully.']);
    }

    // ─── SEASONS ──────────────────────────────────────────────
    public function indexSeasons(Request $request, TransportProvider $provider, TransportRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $seasons = $year->seasons()
            ->orderBy('display_order')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'start_date' => $s->start_date->format('M d'),
                'end_date' => $s->end_date->format('M d'),
                'duration_days' => $s->start_date->diffInDays($s->end_date) + 1,
            ]);

        return response()->json($seasons);
    }

    public function storeSeason(Request $request, TransportProvider $provider, TransportRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'in:High,Low,Peak'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['transport_provider_id'] = $provider->id;
        $data['transport_rate_year_id'] = $year->id;
        $data['display_order'] = $data['display_order'] ?? 0;

        if ($data['start_date'] < $year->valid_from->toDateString() || $data['end_date'] > $year->valid_to->toDateString()) {
            throw ValidationException::withMessages([
                'start_date' => ['Season dates must fit within the selected rate year range.'],
            ]);
        }

        $season = TransportSeason::create($data);

        return response()->json([
            'id' => $season->id,
            'name' => $season->name,
            'message' => 'Season created successfully.',
        ], 201);
    }

    public function updateSeason(Request $request, TransportProvider $provider, TransportSeason $season): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($season->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'in:High,Low,Peak'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $season->update($data);

        return response()->json(['message' => 'Season updated successfully.']);
    }

    public function destroySeason(Request $request, TransportProvider $provider, TransportSeason $season): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($season->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $season->delete();

        return response()->json(['message' => 'Season deleted successfully.']);
    }

    // ─── RATE TYPES ───────────────────────────────────────────
    public function indexRateTypes(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $types = $provider->rateTypes()
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'markup_percentage' => $t->markup_percentage,
                'markup_fixed' => $t->markup_fixed,
                'description' => $t->description,
            ]);

        return response()->json($types);
    }

    public function storeRateType(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'markup_percentage' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'markup_fixed' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'description' => ['nullable', 'string'],
        ]);

        $data['transport_provider_id'] = $provider->id;

        $type = TransportRateType::create($data);

        return response()->json([
            'id' => $type->id,
            'name' => $type->name,
            'message' => 'Rate type created successfully.',
        ], 201);
    }

    public function updateRateType(Request $request, TransportProvider $provider, TransportRateType $rateType): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($rateType->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'markup_percentage' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            'markup_fixed' => ['sometimes', 'numeric', 'min:0', 'max:999999'],
            'description' => ['sometimes', 'string'],
        ]);

        $rateType->update($data);

        return response()->json(['message' => 'Rate type updated successfully.']);
    }

    public function destroyRateType(Request $request, TransportProvider $provider, TransportRateType $rateType): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($rateType->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $rateType->delete();

        return response()->json(['message' => 'Rate type deleted successfully.']);
    }

    // ─── TRANSFER RATES MATRIX ────────────────────────────────
    public function indexTransferRates(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $rates = $provider->transferRates()
            ->with(['route.originDestination', 'route.arrivalDestination', 'vehicleType', 'season'])
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'transfer_route_id' => $r->transfer_route_id,
                'vehicle_type_id' => $r->vehicle_type_id,
                'transport_season_id' => $r->transport_season_id,
                'route' => ($r->route->originDestination?->name ?? 'N/A') . ' → ' . ($r->route->arrivalDestination?->name ?? 'N/A'),
                'vehicle_type' => $r->vehicleType->name,
                'buy_price' => (float) $r->buy_price,
                'sell_price' => (float) $r->sell_price,
                'margin' => ((float) $r->sell_price - (float) $r->buy_price),
                'season' => $r->season?->name ?? 'All',
            ]);

        return response()->json($rates);
    }

    public function storeTransferRate(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'transfer_route_id' => ['required', 'exists:transfer_routes,id'],
            'vehicle_type_id' => ['required', 'exists:vehicle_types,id'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'transport_season_id' => ['nullable', 'exists:transport_seasons,id'],
            'rate_type' => ['nullable', 'string', 'max:50'],
        ]);

        $data['transport_provider_id'] = $provider->id;
        $data['rate_type'] ??= 'per_transfer';

        $route = TransferRoute::findOrFail($data['transfer_route_id']);
        if ($route->transport_provider_id !== $provider->id) {
            throw ValidationException::withMessages([
                'transfer_route_id' => ['Selected route does not belong to this provider.'],
            ]);
        }

        $vehicleType = VehicleType::findOrFail($data['vehicle_type_id']);
        if ($vehicleType->transport_provider_id !== $provider->id) {
            throw ValidationException::withMessages([
                'vehicle_type_id' => ['Selected vehicle type does not belong to this provider.'],
            ]);
        }

        if (!empty($data['transport_season_id'])) {
            $season = TransportSeason::findOrFail($data['transport_season_id']);
            if ($season->transport_provider_id !== $provider->id) {
                throw ValidationException::withMessages([
                    'transport_season_id' => ['Selected season does not belong to this provider.'],
                ]);
            }
        }

        $rate = TransportTransferRate::create($data);

        $this->rateAuditService->record(
            module: 'transport',
            companyId: (int) $provider->company_id,
            providerId: (int) $provider->id,
            providerType: TransportProvider::class,
            entityType: 'transport_transfer_rate',
            entityId: (int) $rate->id,
            action: 'created',
            beforeState: null,
            afterState: $rate->toArray(),
            changedBy: $request->user()?->id,
            source: 'api'
        );

        return response()->json(['id' => $rate->id, 'message' => 'Transfer rate created.'], 201);
    }

    public function updateTransferRate(Request $request, TransportProvider $provider, TransportTransferRate $rate): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($rate->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'buy_price' => ['sometimes', 'numeric', 'min:0'],
            'sell_price' => ['sometimes', 'numeric', 'min:0'],
            'transport_season_id' => ['sometimes', 'nullable', 'exists:transport_seasons,id'],
        ]);

        if (array_key_exists('transport_season_id', $data) && !empty($data['transport_season_id'])) {
            $season = TransportSeason::findOrFail($data['transport_season_id']);
            if ($season->transport_provider_id !== $provider->id) {
                throw ValidationException::withMessages([
                    'transport_season_id' => ['Selected season does not belong to this provider.'],
                ]);
            }
        }

        $before = $rate->toArray();
        $rate->update($data);
        $rate->refresh();

        $this->rateAuditService->record(
            module: 'transport',
            companyId: (int) $provider->company_id,
            providerId: (int) $provider->id,
            providerType: TransportProvider::class,
            entityType: 'transport_transfer_rate',
            entityId: (int) $rate->id,
            action: 'updated',
            beforeState: $before,
            afterState: $rate->toArray(),
            changedBy: $request->user()?->id,
            source: 'api'
        );

        return response()->json(['message' => 'Transfer rate updated.']);
    }

    public function destroyTransferRate(Request $request, TransportProvider $provider, TransportTransferRate $rate): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($rate->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $before = $rate->toArray();
        $rate->delete();

        $this->rateAuditService->record(
            module: 'transport',
            companyId: (int) $provider->company_id,
            providerId: (int) $provider->id,
            providerType: TransportProvider::class,
            entityType: 'transport_transfer_rate',
            entityId: (int) ($before['id'] ?? 0),
            action: 'deleted',
            beforeState: $before,
            afterState: null,
            changedBy: $request->user()?->id,
            source: 'api'
        );

        return response()->json(['message' => 'Transfer rate deleted.']);
    }

    // ─── PAYMENT POLICIES ──────────────────────────────────────
    public function indexPaymentPolicies(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $policies = $provider->paymentPolicies()
            ->orderBy('days_before_arrival')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'days_before_arrival' => $p->days_before_arrival,
                'percentage_due' => $p->percentage_due,
            ]);

        return response()->json($policies);
    }

    public function storePaymentPolicy(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'days_before_arrival' => ['required', 'integer', 'min:0'],
            'percentage_due' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['transport_provider_id'] = $provider->id;

        $policy = TransportPaymentPolicy::create($data);

        return response()->json(['id' => $policy->id, 'message' => 'Payment policy created.'], 201);
    }

    public function updatePaymentPolicy(Request $request, TransportProvider $provider, TransportPaymentPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'days_before_arrival' => ['sometimes', 'integer', 'min:0'],
            'percentage_due' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $policy->update($data);

        return response()->json(['message' => 'Payment policy updated.']);
    }

    public function destroyPaymentPolicy(Request $request, TransportProvider $provider, TransportPaymentPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $policy->delete();

        return response()->json(['message' => 'Payment policy deleted.']);
    }

    // ─── CANCELLATION POLICIES ────────────────────────────────
    public function indexCancellationPolicies(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $policies = $provider->cancellationPolicies()
            ->with('season')
            ->orderBy('days_before_travel')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'days_before_travel' => $p->days_before_travel,
                'penalty_percentage' => $p->penalty_percentage,
                'season_name' => $p->season?->name ?? 'All Seasons',
            ]);

        return response()->json($policies);
    }

    public function storeCancellationPolicy(Request $request, TransportProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'days_before_travel' => ['required', 'integer', 'min:0'],
            'penalty_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'transport_season_id' => ['nullable', 'exists:transport_seasons,id'],
        ]);

        if (!empty($data['transport_season_id'])) {
            $season = TransportSeason::findOrFail($data['transport_season_id']);
            if ($season->transport_provider_id !== $provider->id) {
                throw ValidationException::withMessages([
                    'transport_season_id' => ['Selected season does not belong to this provider.'],
                ]);
            }
        }

        $data['transport_provider_id'] = $provider->id;

        $policy = TransportCancellationPolicy::create($data);

        return response()->json(['id' => $policy->id, 'message' => 'Cancellation policy created.'], 201);
    }

    public function updateCancellationPolicy(Request $request, TransportProvider $provider, TransportCancellationPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'days_before_travel' => ['sometimes', 'integer', 'min:0'],
            'penalty_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'transport_season_id' => ['sometimes', 'nullable', 'exists:transport_seasons,id'],
        ]);

        if (array_key_exists('transport_season_id', $data) && !empty($data['transport_season_id'])) {
            $season = TransportSeason::findOrFail($data['transport_season_id']);
            if ($season->transport_provider_id !== $provider->id) {
                throw ValidationException::withMessages([
                    'transport_season_id' => ['Selected season does not belong to this provider.'],
                ]);
            }
        }

        $policy->update($data);

        return response()->json(['message' => 'Cancellation policy updated.']);
    }

    public function destroyCancellationPolicy(Request $request, TransportProvider $provider, TransportCancellationPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->transport_provider_id !== $provider->id) {
            abort(403);
        }

        $policy->delete();

        return response()->json(['message' => 'Cancellation policy deleted.']);
    }

    private function authorizeCompany(Request $request, TransportProvider $provider): void
    {
        if ($provider->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
