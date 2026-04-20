<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MasterData\AircraftType;
use App\Models\MasterData\CharterFlight;
use App\Models\MasterData\Destination;
use App\Models\MasterData\FlightChildPricing;
use App\Models\MasterData\FlightPolicy;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\FlightRoute;
use App\Models\MasterData\FlightSeasonalRate;
use App\Models\MasterData\ScheduledFlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlightProviderController extends Controller
{
    private function companyId(): ?int
    {
        return Auth::user()->company_id;
    }

    private function isSuperAdmin(): bool
    {
        return Auth::user()->isSuperAdmin();
    }

    private function scopedQuery($model)
    {
        return $model::query();
    }

    private function resolveCompanyId(Request $request): int
    {
        return $this->isSuperAdmin()
            ? (int) $request->input('company_id')
            : $this->companyId();
    }

    private function companyRules(): array
    {
        return $this->isSuperAdmin()
            ? ['company_id' => 'required|exists:companies,id']
            : [];
    }

    private function companiesForForm(): \Illuminate\Support\Collection
    {
        return $this->isSuperAdmin() ? Company::orderBy('name')->get() : collect();
    }

    private function authorize(FlightProvider $provider): void
    {
        if (!$this->isSuperAdmin() && $provider->company_id !== $this->companyId()) {
            abort(403);
        }
    }

    // ─── List ──────────────────────────────────────────────────

    public function index()
    {
        $providers = $this->scopedQuery(FlightProvider::class)
            ->withCount(['routes', 'aircraftTypes', 'scheduledFlights'])
            ->orderBy('name')
            ->get();
        $companies = $this->companiesForForm();
        return view('pages.flight-providers', compact('providers', 'companies'));
    }

    // ─── Store ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'vat_type' => 'nullable|in:inclusive,exclusive,exempt',
            'markup' => 'nullable|numeric|min:0',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        $provider = FlightProvider::create($data);
        return redirect('/flight-providers/' . $provider->id . '/edit')->with('success', 'Flight provider created.');
    }

    // ─── Edit ──────────────────────────────────────────────────

    public function edit(FlightProvider $provider)
    {
        $this->authorize($provider);
        $provider->load([
            'aircraftTypes', 'routes.originDestination', 'routes.arrivalDestination',
            'seasonalRates.route', 'seasonalRates.aircraftType',
            'scheduledFlights.route', 'scheduledFlights.aircraftType',
            'charterFlights.route', 'charterFlights.aircraftType',
            'childPricing', 'policies',
        ]);
        $destinations = $this->scopedQuery(Destination::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        return view('pages.flight-provider-form', [
            'provider' => $provider,
            'destinations' => $destinations,
            'companies' => $companies,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'vat_type' => 'nullable|in:inclusive,exclusive,exempt',
            'markup' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $provider->update($data);
        return back()->with('success', 'Flight provider updated.');
    }

    public function delete(FlightProvider $provider)
    {
        $this->authorize($provider);
        $provider->delete();
        return redirect('/flight-providers')->with('success', 'Flight provider deleted.');
    }

    // ─── Aircraft Types ────────────────────────────────────────

    public function storeAircraftType(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);
        $provider->aircraftTypes()->create($data);
        return back()->with('success', 'Aircraft type added.');
    }

    public function deleteAircraftType(FlightProvider $provider, AircraftType $type)
    {
        $this->authorize($provider);
        $type->delete();
        return back()->with('success', 'Aircraft type deleted.');
    }

    // ─── Routes ────────────────────────────────────────────────

    public function storeRoute(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'origin_destination_id' => 'nullable|exists:destinations,id',
            'arrival_destination_id' => 'nullable|exists:destinations,id',
            'origin_name' => 'nullable|string|max:255',
            'arrival_name' => 'nullable|string|max:255',
            'flight_duration_minutes' => 'nullable|integer|min:1',
        ]);
        $provider->routes()->create($data);
        return back()->with('success', 'Route added.');
    }

    public function deleteRoute(FlightProvider $provider, FlightRoute $route)
    {
        $this->authorize($provider);
        $route->delete();
        return back()->with('success', 'Route deleted.');
    }

    // ─── Seasonal Rates ────────────────────────────────────────

    public function storeSeasonalRate(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'flight_route_id' => 'nullable|exists:flight_routes,id',
            'aircraft_type_id' => 'nullable|exists:aircraft_types,id',
            'season_name' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'adult_rate' => 'required|numeric|min:0',
            'child_rate' => 'nullable|numeric|min:0',
            'infant_rate' => 'nullable|numeric|min:0',
            'charter_rate' => 'nullable|numeric|min:0',
            'rate_type' => 'required|in:scheduled,charter',
            'currency' => 'nullable|string|size:3',
        ]);
        $provider->seasonalRates()->create($data);
        return back()->with('success', 'Seasonal rate added.');
    }

    public function deleteSeasonalRate(FlightProvider $provider, FlightSeasonalRate $rate)
    {
        $this->authorize($provider);
        $rate->delete();
        return back()->with('success', 'Seasonal rate deleted.');
    }

    // ─── Scheduled Flights ─────────────────────────────────────

    public function storeScheduledFlight(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'flight_route_id' => 'nullable|exists:flight_routes,id',
            'aircraft_type_id' => 'nullable|exists:aircraft_types,id',
            'flight_number' => 'nullable|string|max:20',
            'departure_time' => 'nullable|date_format:H:i',
            'arrival_time' => 'nullable|date_format:H:i',
            'frequency' => 'required|in:daily,weekdays,specific_days',
            'operating_days' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $provider->scheduledFlights()->create($data);
        return back()->with('success', 'Scheduled flight added.');
    }

    public function deleteScheduledFlight(FlightProvider $provider, ScheduledFlight $flight)
    {
        $this->authorize($provider);
        $flight->delete();
        return back()->with('success', 'Scheduled flight deleted.');
    }

    // ─── Charter Flights ───────────────────────────────────────

    public function storeCharterFlight(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'flight_route_id' => 'nullable|exists:flight_routes,id',
            'aircraft_type_id' => 'nullable|exists:aircraft_types,id',
            'min_pax' => 'required|integer|min:1',
            'total_charter_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $provider->charterFlights()->create($data);
        return back()->with('success', 'Charter flight added.');
    }

    public function deleteCharterFlight(FlightProvider $provider, CharterFlight $flight)
    {
        $this->authorize($provider);
        $flight->delete();
        return back()->with('success', 'Charter flight deleted.');
    }

    // ─── Child Pricing ─────────────────────────────────────────

    public function storeChildPricing(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'min_age' => 'required|integer|min:0',
            'max_age' => 'required|integer|min:0',
            'pricing_type' => 'required|in:percentage,fixed,free',
            'value' => 'required|numeric|min:0',
        ]);
        $provider->childPricing()->create($data);
        return back()->with('success', 'Child pricing added.');
    }

    public function deleteChildPricing(FlightProvider $provider, FlightChildPricing $pricing)
    {
        $this->authorize($provider);
        $pricing->delete();
        return back()->with('success', 'Child pricing deleted.');
    }

    // ─── Policies ──────────────────────────────────────────────

    public function storePolicy(Request $request, FlightProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'policy_type' => 'required|in:baggage,cancellation,rebooking,general',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        $provider->policies()->create($data);
        return back()->with('success', 'Policy added.');
    }

    public function deletePolicy(FlightProvider $provider, FlightPolicy $policy)
    {
        $this->authorize($provider);
        $policy->delete();
        return back()->with('success', 'Policy deleted.');
    }
}
