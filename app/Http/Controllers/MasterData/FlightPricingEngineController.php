<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\FlightRateYear;
use App\Models\MasterData\FlightSeason;
use App\Models\MasterData\FlightRateType;
use App\Models\MasterData\ScheduledFlight;
use App\Models\MasterData\FlightCharterRate;
use App\Models\MasterData\FlightPaymentPolicy;
use App\Models\MasterData\FlightCancellationPolicy;
use App\Services\Pricing\RateAuditVersioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class FlightPricingEngineController extends Controller
{
    use TenantScoped;

    public function __construct(
        private readonly RateAuditVersioningService $rateAuditService,
    ) {
    }

    // ─── YEARS ────────────────────────────────────────────────
    public function indexYears(Request $request, FlightProvider $provider): JsonResponse
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

    public function storeYear(Request $request, FlightProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after:valid_from'],
            'status' => ['nullable', 'in:draft,active,archived'],
        ]);

        $data['flight_provider_id'] = $provider->id;
        $data['status'] = $data['status'] ?? 'draft';

        $year = FlightRateYear::create($data);

        return response()->json([
            'id' => $year->id,
            'year' => $year->year,
            'message' => 'Year created successfully.',
        ], 201);
    }

    public function updateYear(Request $request, FlightProvider $provider, FlightRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->flight_provider_id !== $provider->id) {
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

    public function destroyYear(Request $request, FlightProvider $provider, FlightRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $year->delete();

        return response()->json(['message' => 'Year deleted successfully.']);
    }

    // ─── SEASONS ──────────────────────────────────────────────
    public function indexSeasons(Request $request, FlightProvider $provider, FlightRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->flight_provider_id !== $provider->id) {
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

    public function storeSeason(Request $request, FlightProvider $provider, FlightRateYear $year): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($year->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'in:High,Low,Peak'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['flight_provider_id'] = $provider->id;
        $data['flight_rate_year_id'] = $year->id;
        $data['display_order'] = $data['display_order'] ?? 0;

        if ($data['start_date'] < $year->valid_from->toDateString() || $data['end_date'] > $year->valid_to->toDateString()) {
            throw ValidationException::withMessages([
                'start_date' => ['Season dates must fit within the selected rate year range.'],
            ]);
        }

        $season = FlightSeason::create($data);

        return response()->json([
            'id' => $season->id,
            'name' => $season->name,
            'message' => 'Season created successfully.',
        ], 201);
    }

    public function updateSeason(Request $request, FlightProvider $provider, FlightSeason $season): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($season->flight_provider_id !== $provider->id) {
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

    public function destroySeason(Request $request, FlightProvider $provider, FlightSeason $season): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($season->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $season->delete();

        return response()->json(['message' => 'Season deleted successfully.']);
    }

    // ─── RATE TYPES ───────────────────────────────────────────
    public function indexRateTypes(Request $request, FlightProvider $provider): JsonResponse
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

    public function storeRateType(Request $request, FlightProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'markup_percentage' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'markup_fixed' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'description' => ['nullable', 'string'],
        ]);

        $data['flight_provider_id'] = $provider->id;

        $type = FlightRateType::create($data);

        $this->rateAuditService->record(
            module: 'flight',
            companyId: (int) $provider->company_id,
            providerId: (int) $provider->id,
            providerType: FlightProvider::class,
            entityType: 'flight_rate_type',
            entityId: (int) $type->id,
            action: 'created',
            beforeState: null,
            afterState: $type->toArray(),
            changedBy: $request->user()?->id,
            source: 'api'
        );

        return response()->json([
            'id' => $type->id,
            'name' => $type->name,
            'message' => 'Rate type created successfully.',
        ], 201);
    }

    public function updateRateType(Request $request, FlightProvider $provider, FlightRateType $rateType): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($rateType->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'markup_percentage' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            'markup_fixed' => ['sometimes', 'numeric', 'min:0', 'max:999999'],
            'description' => ['sometimes', 'string'],
        ]);

        $before = $rateType->toArray();
        $rateType->update($data);
        $rateType->refresh();

        $this->rateAuditService->record(
            module: 'flight',
            companyId: (int) $provider->company_id,
            providerId: (int) $provider->id,
            providerType: FlightProvider::class,
            entityType: 'flight_rate_type',
            entityId: (int) $rateType->id,
            action: 'updated',
            beforeState: $before,
            afterState: $rateType->toArray(),
            changedBy: $request->user()?->id,
            source: 'api'
        );

        return response()->json(['message' => 'Rate type updated successfully.']);
    }

    public function destroyRateType(Request $request, FlightProvider $provider, FlightRateType $rateType): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($rateType->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $before = $rateType->toArray();
        $rateType->delete();

        $this->rateAuditService->record(
            module: 'flight',
            companyId: (int) $provider->company_id,
            providerId: (int) $provider->id,
            providerType: FlightProvider::class,
            entityType: 'flight_rate_type',
            entityId: (int) ($before['id'] ?? 0),
            action: 'deleted',
            beforeState: $before,
            afterState: null,
            changedBy: $request->user()?->id,
            source: 'api'
        );

        return response()->json(['message' => 'Rate type deleted successfully.']);
    }

    // ─── PAYMENT POLICIES ──────────────────────────────────────
    public function indexPaymentPolicies(Request $request, FlightProvider $provider): JsonResponse
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

    public function storePaymentPolicy(Request $request, FlightProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'days_before_arrival' => ['required', 'integer', 'min:0'],
            'percentage_due' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['flight_provider_id'] = $provider->id;

        $policy = FlightPaymentPolicy::create($data);

        return response()->json(['id' => $policy->id, 'message' => 'Payment policy created.'], 201);
    }

    public function updatePaymentPolicy(Request $request, FlightProvider $provider, FlightPaymentPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'days_before_arrival' => ['sometimes', 'integer', 'min:0'],
            'percentage_due' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        $policy->update($data);

        return response()->json(['message' => 'Payment policy updated.']);
    }

    public function destroyPaymentPolicy(Request $request, FlightProvider $provider, FlightPaymentPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $policy->delete();

        return response()->json(['message' => 'Payment policy deleted.']);
    }

    // ─── CANCELLATION POLICIES ────────────────────────────────
    public function indexCancellationPolicies(Request $request, FlightProvider $provider): JsonResponse
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

    public function storeCancellationPolicy(Request $request, FlightProvider $provider): JsonResponse
    {
        $this->authorizeCompany($request, $provider);

        $data = $request->validate([
            'days_before_travel' => ['required', 'integer', 'min:0'],
            'penalty_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'flight_season_id' => ['nullable', 'exists:flight_seasons,id'],
        ]);

        if (!empty($data['flight_season_id'])) {
            $season = FlightSeason::findOrFail($data['flight_season_id']);
            if ($season->flight_provider_id !== $provider->id) {
                throw ValidationException::withMessages([
                    'flight_season_id' => ['Selected season does not belong to this provider.'],
                ]);
            }
        }

        $data['flight_provider_id'] = $provider->id;

        $policy = FlightCancellationPolicy::create($data);

        return response()->json(['id' => $policy->id, 'message' => 'Cancellation policy created.'], 201);
    }

    public function updateCancellationPolicy(Request $request, FlightProvider $provider, FlightCancellationPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $data = $request->validate([
            'days_before_travel' => ['sometimes', 'integer', 'min:0'],
            'penalty_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'flight_season_id' => ['sometimes', 'nullable', 'exists:flight_seasons,id'],
        ]);

        if (array_key_exists('flight_season_id', $data) && !empty($data['flight_season_id'])) {
            $season = FlightSeason::findOrFail($data['flight_season_id']);
            if ($season->flight_provider_id !== $provider->id) {
                throw ValidationException::withMessages([
                    'flight_season_id' => ['Selected season does not belong to this provider.'],
                ]);
            }
        }

        $policy->update($data);

        return response()->json(['message' => 'Cancellation policy updated.']);
    }

    public function destroyCancellationPolicy(Request $request, FlightProvider $provider, FlightCancellationPolicy $policy): JsonResponse
    {
        $this->authorizeCompany($request, $provider);
        if ($policy->flight_provider_id !== $provider->id) {
            abort(403);
        }

        $policy->delete();

        return response()->json(['message' => 'Cancellation policy deleted.']);
    }

    private function authorizeCompany(Request $request, FlightProvider $provider): void
    {
        if ($provider->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
