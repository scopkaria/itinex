<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Country;
use App\Models\Region;
use App\Models\Itinerary\Itinerary;
use App\Models\MasterData\Activity;
use App\Models\MasterData\Destination;
use App\Models\MasterData\Extra;
use App\Models\MasterData\Flight;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\MealPlan;
use App\Models\MasterData\DestinationFee;
use App\Models\MasterData\RoomType;
use App\Models\MasterData\Vehicle;
use App\Models\User;
use App\Models\Itinerary\ItineraryItem;
use App\Services\CostSheetService;
use App\Services\ItineraryService;
use App\Services\ProfitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebController extends Controller
{
    private function companyId(): ?int
    {
        return Auth::user()->company_id;
    }

    private function isSuperAdmin(): bool
    {
        return Auth::user()->isSuperAdmin();
    }

    private function scopedQuery($model, bool $tenantOnly = false)
    {
        $query = $model::query();
        if ($tenantOnly && !$this->isSuperAdmin()) {
            $query->where('company_id', $this->companyId());
        }
        return $query;
    }

    /**
     * Resolve company_id: super admins must submit it; regular users use their own.
     */
    private function resolveCompanyId(Request $request): int
    {
        if ($this->isSuperAdmin()) {
            return (int) $request->input('company_id');
        }
        return $this->companyId();
    }

    /**
     * Validation rules for company_id (only required for super admins).
     */
    private function companyRules(): array
    {
        return $this->isSuperAdmin()
            ? ['company_id' => 'required|exists:companies,id']
            : [];
    }

    /**
     * Pass companies list for views (super admin gets all, others get empty).
     */
    private function companiesForForm(): \Illuminate\Support\Collection
    {
        return $this->isSuperAdmin()
            ? Company::orderBy('name')->get()
            : collect();
    }

    /**
     * Countries filtered by company access + active status.
     * Super admins see all active countries.
     */
    private function countriesForForm(?int $companyId = null): \Illuminate\Support\Collection
    {
        $query = Country::with(['regions' => fn($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('name');

        if (!$this->isSuperAdmin()) {
            $companyId = $this->companyId();
        }

        if ($companyId) {
            $company = Company::find($companyId);
            if ($company && $company->countries()->exists()) {
                $query->whereHas('companies', fn($q) => $q->where('companies.id', $companyId));
            }
        }

        return $query->get();
    }

    // ─── Destinations ──────────────────────────────────────────

    public function destinations()
    {
        $destinations = $this->scopedQuery(Destination::class)
            ->with(['countryRef', 'regionRef'])
            ->withCount('fees')
            ->orderBy('name')
            ->get();
        return view('pages.destinations', compact('destinations'));
    }

    public function createDestination()
    {
        $companies = $this->companiesForForm();
        $countries = $this->countriesForForm();
        return view('pages.destination-form', [
            'destination' => null,
            'rates' => collect(),
            'companies' => $companies,
            'countries' => $countries,
            'mode' => 'create',
        ]);
    }

    public function storeDestination(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'category' => 'required|in:national_park,conservancy,reserve,marine_park,other',
            'supplier' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        $country = Country::find($data['country_id']);
        $data['country'] = $country->name;
        $data['region'] = isset($data['region_id']) ? Region::find($data['region_id'])?->name : null;
        $destination = Destination::create($data);
        return redirect('/destinations/' . $destination->id . '/edit')->with('success', 'Destination created. Now add rates.');
    }

    public function editDestination(Destination $destination)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }
        $rates = $destination->fees()->orderBy('season_name')->get();
        $companies = $this->companiesForForm();
        $countries = $this->countriesForForm($destination->company_id);
        $galleryImages = $destination->media()->orderBy('sort_order')->get();
        return view('pages.destination-form', [
            'destination' => $destination,
            'rates' => $rates,
            'companies' => $companies,
            'countries' => $countries,
            'galleryImages' => $galleryImages,
            'mode' => 'edit',
        ]);
    }

    public function updateDestination(Request $request, Destination $destination)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'category' => 'required|in:national_park,conservancy,reserve,marine_park,other',
            'supplier' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);
        $country = Country::find($data['country_id']);
        $data['country'] = $country->name;
        $data['region'] = isset($data['region_id']) ? Region::find($data['region_id'])?->name : null;
        $destination->update($data);
        return back()->with('success', 'Destination updated.');
    }

    public function cloneDestination(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:destinations,id',
            'name' => 'required|string|max:255',
        ]);

        $source = Destination::with('fees')->findOrFail($request->source_id);
        if (!$this->isSuperAdmin() && $source->company_id !== $this->companyId()) {
            abort(403);
        }

        $clone = $source->replicate(['id', 'created_at', 'updated_at']);
        $clone->name = $request->name;
        $clone->save();

        foreach ($source->fees as $fee) {
            $newFee = $fee->replicate(['id', 'created_at', 'updated_at']);
            $newFee->destination_id = $clone->id;
            $newFee->save();
        }

        return redirect('/destinations/' . $clone->id . '/edit')->with('success', 'Destination cloned successfully.');
    }

    public function deleteDestination(Destination $destination)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }
        $destination->delete();
        return redirect('/destinations')->with('success', 'Destination deleted.');
    }

    // ─── Destination Rates ─────────────────────────────────────

    public function storeDestinationFee(Request $request, Destination $destination)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }
        $data = $request->validate([
            'fee_type' => 'required|string|max:100',
            'season_name' => 'required|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'nr_adult' => 'required|numeric|min:0',
            'nr_child' => 'required|numeric|min:0',
            'resident_adult' => 'required|numeric|min:0',
            'resident_child' => 'required|numeric|min:0',
            'citizen_adult' => 'required|numeric|min:0',
            'citizen_child' => 'required|numeric|min:0',
            'vehicle_rate' => 'required|numeric|min:0',
            'guide_rate' => 'required|numeric|min:0',
            'vat_type' => 'required|in:inclusive,exclusive,exempted',
            'markup' => 'required|numeric|min:0|max:500',
        ]);
        $data['company_id'] = $destination->company_id;
        $data['destination_id'] = $destination->id;
        DestinationFee::create($data);
        return back()->with('success', 'Rate added.');
    }

    public function updateDestinationFee(Request $request, Destination $destination, DestinationFee $fee)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }
        $data = $request->validate([
            'fee_type' => 'required|string|max:100',
            'season_name' => 'required|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'nr_adult' => 'required|numeric|min:0',
            'nr_child' => 'required|numeric|min:0',
            'resident_adult' => 'required|numeric|min:0',
            'resident_child' => 'required|numeric|min:0',
            'citizen_adult' => 'required|numeric|min:0',
            'citizen_child' => 'required|numeric|min:0',
            'vehicle_rate' => 'required|numeric|min:0',
            'guide_rate' => 'required|numeric|min:0',
            'vat_type' => 'required|in:inclusive,exclusive,exempted',
            'markup' => 'required|numeric|min:0|max:500',
        ]);
        $fee->update($data);
        return back()->with('success', 'Rate updated.');
    }

    public function cloneRatesToYear(Request $request, Destination $destination)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }
        $request->validate(['year_offset' => 'required|integer|min:1|max:5']);
        $offset = (int) $request->year_offset;
        $rates = $destination->fees;
        $count = 0;
        foreach ($rates as $fee) {
            $clone = $fee->replicate(['id', 'created_at', 'updated_at']);
            if ($fee->valid_from) { $clone->valid_from = $fee->valid_from->addYears($offset); }
            if ($fee->valid_to) { $clone->valid_to = $fee->valid_to->addYears($offset); }
            $clone->save();
            $count++;
        }
        return back()->with('success', "Cloned {$count} rates forward by {$offset} year(s).");
    }

    public function deleteDestinationFee(Destination $destination, DestinationFee $fee)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }
        $fee->delete();
        return back()->with('success', 'Rate removed.');
    }

    // ─── Destination Gallery ───────────────────────────────────

    public function uploadDestinationMedia(Request $request, Destination $destination)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }

        $request->validate([
            'images' => 'required|array|max:20',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $maxOrder = $destination->media()->max('sort_order') ?? -1;
        $uploaded = [];

        foreach ($request->file('images') as $file) {
            $path = $file->store('destinations/' . $destination->id, 'public');
            $maxOrder++;
            $media = $destination->media()->create([
                'file_path' => $path,
                'is_cover' => $destination->media()->count() === 0,
                'sort_order' => $maxOrder,
            ]);
            $uploaded[] = $media;
        }

        return response()->json(['success' => true, 'media' => $uploaded]);
    }

    public function deleteDestinationMedia(Destination $destination, \App\Models\MasterData\DestinationMedia $media)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($media->file_path);

        $wasCover = $media->is_cover;
        $media->delete();

        if ($wasCover) {
            $first = $destination->media()->orderBy('sort_order')->first();
            if ($first) {
                $first->update(['is_cover' => true]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function setCoverDestinationMedia(Destination $destination, \App\Models\MasterData\DestinationMedia $media)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }

        $destination->media()->update(['is_cover' => false]);
        $media->update(['is_cover' => true]);

        return response()->json(['success' => true]);
    }

    public function reorderDestinationMedia(Request $request, Destination $destination)
    {
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:destination_media,id',
        ]);

        foreach ($request->order as $index => $id) {
            \App\Models\MasterData\DestinationMedia::where('id', $id)
                ->where('destination_id', $destination->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // ─── Hotels ────────────────────────────────────────────────

    public function hotels()
    {
        $hotels = $this->scopedQuery(Hotel::class)->with('location')->orderBy('name')->get();
        $destinations = $this->scopedQuery(Destination::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        return view('pages.hotels', compact('hotels', 'destinations', 'companies'));
    }

    public function storeHotel(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:destinations,id',
            'category' => 'required|in:budget,midrange,luxury',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        Hotel::create($data);
        return back()->with('success', 'Hotel created.');
    }

    public function deleteHotel(Hotel $hotel)
    {
        if (!$this->isSuperAdmin() && $hotel->company_id !== $this->companyId()) {
            abort(403);
        }
        $hotel->delete();
        return back()->with('success', 'Hotel deleted.');
    }

    // ─── Vehicles ──────────────────────────────────────────────

    public function vehicles()
    {
        $vehicles = $this->scopedQuery(Vehicle::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        return view('pages.vehicles', compact('vehicles', 'companies'));
    }

    public function storeVehicle(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'price_per_day' => 'required|numeric|min:0',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        Vehicle::create($data);
        return back()->with('success', 'Vehicle created.');
    }

    public function deleteVehicle(Vehicle $vehicle)
    {
        if (!$this->isSuperAdmin() && $vehicle->company_id !== $this->companyId()) {
            abort(403);
        }
        $vehicle->delete();
        return back()->with('success', 'Vehicle deleted.');
    }

    // (Park Fees removed — merged into Destinations > Fees)

    // ─── Activities ────────────────────────────────────────────

    public function activities()
    {
        $activities = $this->scopedQuery(Activity::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        return view('pages.activities', compact('activities', 'companies'));
    }

    public function storeActivity(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_person' => 'required|numeric|min:0',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        Activity::create($data);
        return back()->with('success', 'Activity created.');
    }

    public function deleteActivity(Activity $activity)
    {
        if (!$this->isSuperAdmin() && $activity->company_id !== $this->companyId()) {
            abort(403);
        }
        $activity->delete();
        return back()->with('success', 'Activity deleted.');
    }

    // ─── Extras ────────────────────────────────────────────────

    public function extras()
    {
        $extras = $this->scopedQuery(Extra::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        return view('pages.extras', compact('extras', 'companies'));
    }

    public function storeExtra(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        Extra::create($data);
        return back()->with('success', 'Extra created.');
    }

    public function deleteExtra(Extra $extra)
    {
        if (!$this->isSuperAdmin() && $extra->company_id !== $this->companyId()) {
            abort(403);
        }
        $extra->delete();
        return back()->with('success', 'Extra deleted.');
    }

    // ─── Flights ───────────────────────────────────────────────

    public function flights()
    {
        $flights = $this->scopedQuery(Flight::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        return view('pages.flights', compact('flights', 'companies'));
    }

    public function storeFlight(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'origin' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'price_per_person' => 'required|numeric|min:0',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        Flight::create($data);
        return back()->with('success', 'Flight created.');
    }

    public function deleteFlight(Flight $flight)
    {
        if (!$this->isSuperAdmin() && $flight->company_id !== $this->companyId()) {
            abort(403);
        }
        $flight->delete();
        return back()->with('success', 'Flight deleted.');
    }

    // ─── Itineraries ───────────────────────────────────────────

    public function itineraries()
    {
        $itineraries = $this->scopedQuery(Itinerary::class, true)
            ->withCount('days')
            ->orderByDesc('created_at')
            ->get();
        $companies = $this->companiesForForm();
        return view('pages.itineraries', compact('itineraries', 'companies'));
    }

    public function storeItinerary(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'number_of_people' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        $data['user_id'] = Auth::id();
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate = \Carbon\Carbon::parse($data['end_date']);
        $data['total_days'] = $startDate->diffInDays($endDate) + 1;

        $itinerary = Itinerary::create($data);

        // Auto-create days
        for ($i = 0; $i < $data['total_days']; $i++) {
            $itinerary->days()->create([
                'day_number' => $i + 1,
                'date' => $startDate->copy()->addDays($i),
            ]);
        }

        return back()->with('success', 'Itinerary created.');
    }

    public function showItinerary(Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $itinerary->load('days.items');

        // Get master data for dropdowns
        $companyId = $itinerary->company_id;
        $hotelRates = HotelRate::with(['hotel', 'roomType', 'mealPlan'])
            ->where('company_id', $companyId)
            ->get();
        $destinationFees = DestinationFee::with('destination')->get();
        $vehicles = Vehicle::all();
        $flights = Flight::all();
        $activities = Activity::all();
        $extras = Extra::all();

        // Cost sheet
        $costSheet = app(CostSheetService::class)->generate($itinerary);

        return view('pages.itinerary-show', compact(
            'itinerary', 'hotelRates', 'destinationFees', 'vehicles',
            'flights', 'activities', 'extras', 'costSheet'
        ));
    }

    public function deleteItinerary(Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }
        $itinerary->days()->each(fn($d) => $d->items()->delete());
        $itinerary->days()->delete();
        $itinerary->delete();
        return redirect('/itineraries')->with('success', 'Itinerary deleted.');
    }

    // ─── Itinerary Items ───────────────────────────────────────

    public function storeItem(Request $request, Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $data = $request->validate([
            'itinerary_day_id' => 'required|exists:itinerary_days,id',
            'type' => 'required|in:hotel,transport,park_fee,activity,extra,flight',
            'reference_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        // Verify the day belongs to this itinerary
        $day = $itinerary->days()->where('id', $data['itinerary_day_id'])->firstOrFail();

        $item = new ItineraryItem($data);

        // Calculate cost from master data
        $service = app(ItineraryService::class);
        $item->cost = $service->calculateItemCost($item, $itinerary);

        $day->items()->save($item);

        // Recalculate itinerary totals
        $service->recalculate($itinerary);

        return back()->with('success', 'Item added to Day ' . $day->day_number . '.');
    }

    public function deleteItem(Itinerary $itinerary, ItineraryItem $item)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        // Verify item belongs to this itinerary
        $dayIds = $itinerary->days()->pluck('id');
        if (!$dayIds->contains($item->itinerary_day_id)) {
            abort(404);
        }

        $item->delete();

        // Recalculate itinerary totals
        app(ItineraryService::class)->recalculate($itinerary);

        return back()->with('success', 'Item removed.');
    }

    // ─── Markup / Profit ───────────────────────────────────────

    public function applyMarkup(Request $request, Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $request->validate([
            'markup_percentage' => 'required|numeric|min:0|max:500',
        ]);

        app(ProfitService::class)->applyMarkupToItinerary(
            $itinerary,
            (float) $request->markup_percentage
        );

        return back()->with('success', 'Markup of ' . $request->markup_percentage . '% applied.');
    }

    // ─── Geography Manager (super_admin) ─────────────────────

    public function geography()
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $countries = Country::withCount('regions')->orderBy('name')->get();
        $regions = Region::with('country')->orderBy('name')->get();
        $companies = Company::with('countries')->orderBy('name')->get();
        return view('pages.geography', compact('countries', 'regions', 'companies'));
    }

    public function storeCountry(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:5|unique:countries,code',
            'continent' => 'required|string|max:100',
        ]);
        Country::create($data);
        return back()->with('success', 'Country created.');
    }

    public function updateCountry(Request $request, Country $country)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:5|unique:countries,code,' . $country->id,
            'continent' => 'required|string|max:100',
        ]);
        $country->update($data);
        return back()->with('success', 'Country updated.');
    }

    public function toggleCountry(Country $country)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $country->update(['is_active' => !$country->is_active]);
        return back()->with('success', $country->name . ($country->is_active ? ' activated.' : ' deactivated.'));
    }

    public function storeRegion(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:region,circuit,zone',
        ]);
        Region::create($data);
        return back()->with('success', 'Region created.');
    }

    public function updateRegion(Request $request, Region $region)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:region,circuit,zone',
        ]);
        $region->update($data);
        return back()->with('success', 'Region updated.');
    }

    public function toggleRegion(Region $region)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $region->update(['is_active' => !$region->is_active]);
        return back()->with('success', $region->name . ($region->is_active ? ' activated.' : ' deactivated.'));
    }

    public function updateCompanyAccess(Request $request, Company $company)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'country_ids' => 'nullable|array',
            'country_ids.*' => 'exists:countries,id',
        ]);
        $company->countries()->sync($data['country_ids'] ?? []);
        return back()->with('success', 'Access updated for ' . $company->name . '.');
    }

    // ─── Companies (super_admin) ───────────────────────────────

    public function companies()
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $companies = Company::withCount(['users', 'destinations', 'destinationFees', 'hotels', 'vehicles', 'itineraries'])
            ->orderBy('name')->get();
        return view('pages.companies', compact('companies'));
    }

    public function showCompany(Company $company)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $company->loadCount(['users', 'destinations', 'destinationFees', 'hotels', 'vehicles', 'itineraries']);
        $destinations = $company->destinations()->with(['fees', 'countryRef', 'regionRef'])->orderBy('name')->get();
        $hotels = $company->hotels()->with('location')->orderBy('name')->get();
        $vehicles = $company->vehicles()->orderBy('name')->get();
        $users = $company->users()->orderBy('name')->get();
        $itineraries = $company->itineraries()->withCount('days')->orderByDesc('created_at')->take(20)->get();
        return view('pages.company-show', compact('company', 'destinations', 'hotels', 'vehicles', 'users', 'itineraries'));
    }

    public function storeCompany(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);
        Company::create($data);
        return back()->with('success', 'Company created.');
    }

    public function deleteCompany(Company $company)
    {
        if (!$this->isSuperAdmin()) {
            abort(403);
        }
        $company->delete();
        return back()->with('success', 'Company deleted.');
    }

    // ─── Users (admin / super_admin) ───────────────────────────

    public function users()
    {
        if ($this->isSuperAdmin()) {
            $users = User::with('company')->orderBy('name')->get();
            $companies = Company::orderBy('name')->get();
        } else {
            $users = User::with('company')
                ->where('company_id', $this->companyId())
                ->orderBy('name')
                ->get();
            $companies = collect();
        }
        return view('pages.users', compact('users', 'companies'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,staff',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        $data['password'] = Hash::make($data['password']);

        if (!$this->isSuperAdmin()) {
            $data['company_id'] = $this->companyId();
            $data['role'] = 'staff';
        }

        User::create($data);
        return back()->with('success', 'User created.');
    }

    public function deleteUser(User $user)
    {
        if (!$this->isSuperAdmin() && $user->company_id !== $this->companyId()) {
            abort(403);
        }
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
