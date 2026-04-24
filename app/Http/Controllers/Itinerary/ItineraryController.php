<?php

namespace App\Http\Controllers\Itinerary;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\Itinerary\Itinerary;
use App\Models\Itinerary\ItineraryDay;
use App\Models\Itinerary\ItineraryItem;
use App\Models\Itinerary\ItineraryPricingOverride;
use App\Services\ItineraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    use TenantScoped;

    public function __construct(
        private readonly ItineraryService $service,
    ) {}

    /**
     * List all itineraries for the company.
     */
    public function index(Request $request): JsonResponse
    {
        $itineraries = Itinerary::where('company_id', $this->companyId($request))
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($itineraries);
    }

    /**
     * Create a new itinerary.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'number_of_people' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $data['user_id'] = $request->user()->id;

        $start = \Carbon\Carbon::parse($data['start_date']);
        $end = \Carbon\Carbon::parse($data['end_date']);
        $data['total_days'] = $start->diffInDays($end) + 1;

        $itinerary = Itinerary::create($data);

        // Auto-generate day rows
        for ($i = 0; $i < $data['total_days']; $i++) {
            ItineraryDay::create([
                'itinerary_id' => $itinerary->id,
                'day_number' => $i + 1,
                'date' => $start->copy()->addDays($i)->toDateString(),
            ]);
        }

        return response()->json(
            $itinerary->fresh(['days']),
            201
        );
    }

    /**
     * Show full itinerary with days and items.
     */
    public function show(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        $itinerary->load(['days.items', 'user:id,name']);

        return response()->json([
            'itinerary' => $itinerary,
            ...$this->service->summary(
                $itinerary,
                $request->query('partner_type'),
                $request->query('partner_key')
            ),
        ]);
    }

    /**
     * Update itinerary header fields.
     */
    public function update(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        $data = $request->validate([
            'client_name' => ['sometimes', 'string', 'max:255'],
            'number_of_people' => ['sometimes', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $itinerary->update($data);

        // If dates changed, recalculate total_days
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $itinerary->update([
                'total_days' => $itinerary->start_date->diffInDays($itinerary->end_date) + 1,
            ]);
        }

        // If number_of_people changed, recalculate all item costs
        if (isset($data['number_of_people'])) {
            $this->recalculateAllItems($itinerary);
        }

        $itinerary = $this->service->recalculate($itinerary);

        return response()->json([
            'itinerary' => $itinerary,
            ...$this->service->summary($itinerary),
        ]);
    }

    /**
     * Delete an itinerary.
     */
    public function destroy(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);
        $itinerary->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    /**
     * Add a day to the itinerary.
     */
    public function addDay(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        $data = $request->validate([
            'day_number' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
        ]);

        $data['itinerary_id'] = $itinerary->id;
        $day = ItineraryDay::create($data);

        $this->service->recalculate($itinerary);

        return response()->json($day, 201);
    }

    /**
     * Remove a day from the itinerary.
     */
    public function removeDay(Request $request, Itinerary $itinerary, ItineraryDay $day): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        if ($day->itinerary_id !== $itinerary->id) {
            abort(404);
        }

        $day->delete();
        $this->service->recalculate($itinerary);

        return response()->json(['message' => 'Day removed.']);
    }

    /**
     * Add an item to a day and auto-recalculate.
     */
    public function addItem(Request $request, Itinerary $itinerary, ItineraryDay $day): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        if ($day->itinerary_id !== $itinerary->id) {
            abort(404);
        }

        $data = $request->validate([
            'type' => ['required', 'in:hotel,transport,park_fee,activity,extra,flight'],
            'reference_id' => ['required', 'integer'],
            'reference_source' => ['nullable', 'string', 'max:60'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'meta' => ['nullable', 'array'],
        ]);

        $data['itinerary_day_id'] = $day->id;
        $data['quantity'] = $data['quantity'] ?? 1;

        // Create item with zero cost first
        $item = ItineraryItem::create($data);

        // Calculate cost from master data
        $cost = $this->service->calculateItemCost($item, $itinerary);
        $item->update([
            'cost' => $cost,
            'price' => $data['price'] ?? $cost, // default selling price = cost if not set
        ]);

        // Recalculate itinerary totals
        $itinerary = $this->service->recalculate($itinerary);

        return response()->json([
            'item' => $item->fresh(),
            ...$this->service->summary(
                $itinerary,
                $request->query('partner_type'),
                $request->query('partner_key')
            ),
        ], 201);
    }

    /**
     * Update an item's quantity or price and recalculate.
     */
    public function updateItem(Request $request, Itinerary $itinerary, ItineraryDay $day, ItineraryItem $item): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        if ($day->itinerary_id !== $itinerary->id || $item->itinerary_day_id !== $day->id) {
            abort(404);
        }

        $data = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'reference_id' => ['sometimes', 'integer'],
            'reference_source' => ['sometimes', 'nullable', 'string', 'max:60'],
            'meta' => ['sometimes', 'nullable', 'array'],
        ]);

        $item->update($data);

        // Recalculate cost from master data
        $cost = $this->service->calculateItemCost($item->fresh(), $itinerary);
        $item->update(['cost' => $cost]);

        $itinerary = $this->service->recalculate($itinerary);

        return response()->json([
            'item' => $item->fresh(),
            ...$this->service->summary(
                $itinerary,
                $request->query('partner_type'),
                $request->query('partner_key')
            ),
        ]);
    }

    /**
     * Remove an item and recalculate.
     */
    public function removeItem(Request $request, Itinerary $itinerary, ItineraryDay $day, ItineraryItem $item): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        if ($day->itinerary_id !== $itinerary->id || $item->itinerary_day_id !== $day->id) {
            abort(404);
        }

        $item->delete();
        $itinerary = $this->service->recalculate($itinerary);

        return response()->json([
            'message' => 'Item removed.',
            ...$this->service->summary(
                $itinerary,
                $request->query('partner_type'),
                $request->query('partner_key')
            ),
        ]);
    }

    /**
     * Get calculated totals for the itinerary.
     */
    public function totals(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        $itinerary = $this->service->recalculate($itinerary);

        return response()->json($this->service->summary(
            $itinerary,
            $request->query('partner_type'),
            $request->query('partner_key')
        ));
    }

    public function upsertPartnerOverride(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        $data = $request->validate([
            'partner_type' => ['required', 'in:agent,partner'],
            'partner_key' => ['required', 'string', 'max:120'],
            'override_mode' => ['required', 'in:percent,fixed'],
            'override_value' => ['required', 'numeric', 'min:-999999', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $override = ItineraryPricingOverride::query()->updateOrCreate(
            [
                'itinerary_id' => $itinerary->id,
                'company_id' => $itinerary->company_id,
                'partner_type' => $data['partner_type'],
                'partner_key' => $data['partner_key'],
            ],
            [
                'override_mode' => $data['override_mode'],
                'override_value' => $data['override_value'],
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return response()->json([
            'override' => $override,
            ...$this->service->summary($itinerary, $override->partner_type, $override->partner_key),
        ]);
    }

    public function deletePartnerOverride(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorizeCompany($request, $itinerary);

        $data = $request->validate([
            'partner_type' => ['required', 'in:agent,partner'],
            'partner_key' => ['required', 'string', 'max:120'],
        ]);

        ItineraryPricingOverride::query()
            ->where('itinerary_id', $itinerary->id)
            ->where('company_id', $itinerary->company_id)
            ->where('partner_type', $data['partner_type'])
            ->where('partner_key', $data['partner_key'])
            ->delete();

        return response()->json([
            'message' => 'Override removed.',
            ...$this->service->summary($itinerary),
        ]);
    }

    /**
     * Recalculate all item costs (e.g. when number_of_people changes).
     */
    private function recalculateAllItems(Itinerary $itinerary): void
    {
        $itinerary->load('days.items');

        foreach ($itinerary->days as $day) {
            foreach ($day->items as $item) {
                $cost = $this->service->calculateItemCost($item, $itinerary);
                $item->update(['cost' => $cost]);
            }
        }
    }

    private function authorizeCompany(Request $request, Itinerary $itinerary): void
    {
        if ($itinerary->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
