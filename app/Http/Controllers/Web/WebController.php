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
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\FlightRateType;
use App\Models\MasterData\FlightSeason;
use App\Models\MasterData\FlightChildPricing;
use App\Models\MasterData\FlightRoute;
use App\Models\MasterData\ScheduledFlight;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\HotelRate;
use App\Models\MasterData\MealPlan;
use App\Models\MasterData\Package;
use App\Models\MasterData\DestinationFee;
use App\Models\MasterData\ParkFee;
use App\Models\MasterData\ProviderVehicle;
use App\Models\MasterData\RoomType;
use App\Models\MasterData\TransferRoute;
use App\Models\MasterData\Vehicle;
use App\Models\MasterData\TransportProvider;
use App\Models\MasterData\TransportRate;
use App\Models\MasterData\TransportSeason;
use App\Models\MasterData\TransportTransferRate;
use App\Models\User;
use App\Models\Itinerary\ItineraryItem;
use App\Services\CostSheetService;
use App\Services\ItineraryService;
use App\Services\Pricing\SafariPricingBrainService;
use App\Services\ProfitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
            ->with(['countryRef', 'regionRef', 'media'])
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

    // ─── Park Fees / Conservation Fees ───────────────────────

    public function parkFees(Request $request)
    {
        $hasNameColumn = Schema::hasColumn('destination_fees', 'name');
        $seasonColumn = Schema::hasColumn('destination_fees', 'season_name')
            ? 'season_name'
            : (Schema::hasColumn('destination_fees', 'season') ? 'season' : null);

        $parkFees = $this->scopedQuery(ParkFee::class, true)
            ->with('destination:id,name,region,supplier')
            ->when($request->filled('year'), function ($query) use ($request) {
                $year = (int) $request->query('year');
                $query->where(function ($sub) use ($year) {
                    $sub->whereYear('valid_from', $year)
                        ->orWhereYear('valid_to', $year);
                });
            })
            ->when($request->filled('season') && $seasonColumn !== null, function ($query) use ($request, $seasonColumn) {
                $query->where($seasonColumn, $request->query('season'));
            })
            ->get();

        $parkFees = $parkFees
            ->sortBy(function ($fee) use ($hasNameColumn, $seasonColumn) {
                $parkName = $hasNameColumn
                    ? (string) ($fee->name ?? '')
                    : (string) ($fee->destination?->name ?? '');
                $seasonName = $seasonColumn ? (string) ($fee->{$seasonColumn} ?? '') : '';

                return strtolower($parkName . '|' . $seasonName);
            })
            ->values();

        $destinations = $this->scopedQuery(Destination::class, true)
            ->orderBy('name')
            ->get(['id', 'name', 'region', 'supplier']);

        $seasons = $seasonColumn
            ? $this->scopedQuery(ParkFee::class, true)
                ->whereNotNull($seasonColumn)
                ->distinct()
                ->orderBy($seasonColumn)
                ->pluck($seasonColumn)
                ->values()
            : collect();

        return view('pages.park-fees', [
            'parkFees' => $parkFees,
            'destinations' => $destinations,
            'seasons' => $seasons,
            'filters' => $request->only(['year', 'season']),
            'companies' => $this->companiesForForm(),
        ]);
    }

    public function storeParkFee(Request $request)
    {
        $data = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'name' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'season_id' => 'nullable|integer|min:1|max:99',
            'season_name' => 'required|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'resident_adult' => 'required|numeric|min:0',
            'resident_child' => 'required|numeric|min:0',
            'nr_adult' => 'required|numeric|min:0',
            'nr_child' => 'required|numeric|min:0',
            'vehicle_rate' => 'required|numeric|min:0',
            'guide_rate' => 'required|numeric|min:0',
            'markup_type' => 'required|in:percent,fixed',
            'markup' => 'required|numeric|min:0|max:500',
            'vat_type' => 'required|in:inclusive,exclusive,exempted',
            ...$this->companyRules(),
        ]);

        $destination = Destination::findOrFail((int) $data['destination_id']);
        if (!$this->isSuperAdmin() && $destination->company_id !== $this->companyId()) {
            abort(403);
        }

        $data['company_id'] = $this->resolveCompanyId($request);
        $data['fee_type'] = 'Park Fee';
        $data['citizen_adult'] = 0;
        $data['citizen_child'] = 0;

        ParkFee::create($data);

        return back()->with('success', 'Park fee created.');
    }

    public function updateParkFee(Request $request, ParkFee $parkFee)
    {
        if (!$this->isSuperAdmin() && $parkFee->company_id !== $this->companyId()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'season_id' => 'nullable|integer|min:1|max:99',
            'season_name' => 'required|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'resident_adult' => 'required|numeric|min:0',
            'resident_child' => 'required|numeric|min:0',
            'nr_adult' => 'required|numeric|min:0',
            'nr_child' => 'required|numeric|min:0',
            'vehicle_rate' => 'required|numeric|min:0',
            'guide_rate' => 'required|numeric|min:0',
            'markup_type' => 'required|in:percent,fixed',
            'markup' => 'required|numeric|min:0|max:500',
            'vat_type' => 'required|in:inclusive,exclusive,exempted',
        ]);

        $parkFee->update($data);

        return back()->with('success', 'Park fee updated.');
    }

    public function deleteParkFee(ParkFee $parkFee)
    {
        if (!$this->isSuperAdmin() && $parkFee->company_id !== $this->companyId()) {
            abort(403);
        }

        $parkFee->delete();
        return back()->with('success', 'Park fee deleted.');
    }

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

    // ─── Packages ─────────────────────────────────────────────

    public function packages(Request $request)
    {
        $packages = $this->scopedQuery(Package::class, true)
            ->with('destination:id,name')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->query('q'));
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', '%' . $term . '%')
                        ->orWhere('code', 'like', '%' . $term . '%')
                        ->orWhere('notes', 'like', '%' . $term . '%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->query('status') === 'active');
            })
            ->when($request->filled('price_mode'), function ($query) use ($request) {
                $query->where('price_mode', $request->query('price_mode'));
            })
            ->when($request->filled('destination_id'), function ($query) use ($request) {
                $query->where('destination_id', (int) $request->query('destination_id'));
            })
            ->orderBy('name')
            ->get();
        $destinations = $this->scopedQuery(Destination::class, true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $companies = $this->companiesForForm();

        return view('pages.packages', [
            'packages' => $packages,
            'destinations' => $destinations,
            'companies' => $companies,
            'filters' => $request->only(['q', 'status', 'price_mode', 'destination_id']),
        ]);
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:60',
            'destination_id' => 'nullable|exists:destinations,id',
            'nights' => 'required|integer|min:1|max:365',
            'price_mode' => 'required|in:per_person,per_group',
            'base_price' => 'required|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'discount_mode' => 'required|in:none,percent,fixed',
            'discount_value' => 'nullable|numeric|min:0|max:999999',
            'currency' => 'required|string|size:3',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            ...$this->companyRules(),
        ]);

        $data['company_id'] = $this->resolveCompanyId($request);
        $data['markup_percentage'] = $data['markup_percentage'] ?? 0;
        $data['discount_value'] = $data['discount_value'] ?? 0;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['currency'] = strtoupper($data['currency']);

        Package::create($data);

        return back()->with('success', 'Package created.');
    }

    public function updatePackage(Request $request, Package $package)
    {
        if (!$this->isSuperAdmin() && $package->company_id !== $this->companyId()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:60',
            'destination_id' => 'nullable|exists:destinations,id',
            'nights' => 'required|integer|min:1|max:365',
            'price_mode' => 'required|in:per_person,per_group',
            'base_price' => 'required|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'discount_mode' => 'required|in:none,percent,fixed',
            'discount_value' => 'nullable|numeric|min:0|max:999999',
            'currency' => 'required|string|size:3',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $data['markup_percentage'] = $data['markup_percentage'] ?? 0;
        $data['discount_value'] = $data['discount_value'] ?? 0;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['currency'] = strtoupper($data['currency']);

        $package->update($data);

        return back()->with('success', 'Package updated.');
    }

    public function deletePackage(Package $package)
    {
        if (!$this->isSuperAdmin() && $package->company_id !== $this->companyId()) {
            abort(403);
        }

        $package->delete();

        return back()->with('success', 'Package deleted.');
    }

    public function bulkPackages(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:activate,deactivate,duplicate,delete',
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'integer|exists:packages,id',
        ]);

        $query = Package::query()->whereIn('id', $data['package_ids']);

        if (! $this->isSuperAdmin()) {
            $query->where('company_id', $this->companyId());
        }

        $packages = $query->get();

        if ($packages->isEmpty()) {
            return back()->with('error', 'No eligible packages found for the selected action.');
        }

        if ($data['action'] === 'activate') {
            $query->update(['is_active' => true]);
            return back()->with('success', 'Selected packages activated.');
        }

        if ($data['action'] === 'deactivate') {
            $query->update(['is_active' => false]);
            return back()->with('success', 'Selected packages deactivated.');
        }

        if ($data['action'] === 'delete') {
            $deleted = $packages->count();
            $query->delete();
            return back()->with('success', "{$deleted} package(s) deleted.");
        }

        $duplicates = 0;
        foreach ($packages as $package) {
            $clone = $package->replicate(['id', 'created_at', 'updated_at']);
            $clone->name = $package->name . ' Copy';
            $clone->code = $this->nextPackageCopyCode($package);
            $clone->save();
            $duplicates++;
        }

        return back()->with('success', "{$duplicates} package(s) duplicated.");
    }

    public function importPackagesCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
            ...$this->companyRules(),
        ]);

        $companyId = $this->resolveCompanyId($request);
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $expected = ['name', 'code', 'destination', 'nights', 'price_mode', 'base_price', 'markup_percentage', 'discount_mode', 'discount_value', 'currency', 'is_active', 'notes'];
        if ($header !== $expected) {
            fclose($handle);
            return back()->with('error', 'CSV must have columns: ' . implode(', ', $expected));
        }

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($value) => $value !== null && $value !== '')) === 0) {
                continue;
            }

            $rowData = array_combine($expected, $row);
            $destinationId = null;
            if (!empty($rowData['destination'])) {
                $destination = Destination::query()
                    ->where('company_id', $companyId)
                    ->where('name', $rowData['destination'])
                    ->first();
                $destinationId = $destination?->id;
            }

            Package::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => $rowData['code'] ?: null,
                    'name' => $rowData['name'],
                ],
                [
                    'destination_id' => $destinationId,
                    'nights' => max(1, (int) $rowData['nights']),
                    'price_mode' => in_array($rowData['price_mode'], ['per_person', 'per_group'], true) ? $rowData['price_mode'] : 'per_person',
                    'base_price' => (float) $rowData['base_price'],
                    'markup_percentage' => (float) ($rowData['markup_percentage'] ?: 0),
                    'discount_mode' => in_array($rowData['discount_mode'], ['none', 'percent', 'fixed'], true) ? $rowData['discount_mode'] : 'none',
                    'discount_value' => (float) ($rowData['discount_value'] ?: 0),
                    'currency' => strtoupper($rowData['currency'] ?: 'USD'),
                    'is_active' => in_array(strtolower((string) $rowData['is_active']), ['1', 'true', 'yes', 'active'], true),
                    'notes' => $rowData['notes'] ?: null,
                ]
            );

            $count++;
        }

        fclose($handle);

        return back()->with('success', "{$count} package row(s) imported.");
    }

    public function exportPackagesCsv(Request $request)
    {
        $query = $this->scopedQuery(Package::class, true)->with('destination:id,name')->orderBy('name');

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'code', 'destination', 'nights', 'price_mode', 'base_price', 'markup_percentage', 'discount_mode', 'discount_value', 'currency', 'is_active', 'notes']);

            foreach ($query->get() as $package) {
                fputcsv($handle, [
                    $package->name,
                    $package->code,
                    $package->destination?->name,
                    $package->nights,
                    $package->price_mode,
                    $package->base_price,
                    $package->markup_percentage,
                    $package->discount_mode,
                    $package->discount_value,
                    $package->currency,
                    $package->is_active ? '1' : '0',
                    $package->notes,
                ]);
            }

            fclose($handle);
        }, 'packages-export.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function templatePackagesCsv()
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'code', 'destination', 'nights', 'price_mode', 'base_price', 'markup_percentage', 'discount_mode', 'discount_value', 'currency', 'is_active', 'notes']);
            fputcsv($handle, ['Northern Highlights', 'PKG-NORTH-001', 'Serengeti', '4', 'per_person', '850', '12', 'percent', '5', 'USD', '1', 'Popular north circuit package']);
            fputcsv($handle, ['Family Escape', 'PKG-FAM-002', 'Ngorongoro', '3', 'per_group', '2400', '8', 'fixed', '150', 'USD', '1', 'Family group offer sample']);
            fclose($handle);
        }, 'packages-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function nextPackageCopyCode(Package $package): ?string
    {
        if (! $package->code) {
            return null;
        }

        $baseCode = $package->code . '-COPY';
        $code = $baseCode;
        $suffix = 2;

        while (Package::query()
            ->where('company_id', $package->company_id)
            ->where('code', $code)
            ->exists()) {
            $code = $baseCode . '-' . $suffix;
            $suffix++;
        }

        return $code;
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
            ->whereHas('hotel', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->get();
        $destinationFees = DestinationFee::with('destination')
            ->where('company_id', $companyId)
            ->get();
        $vehicles = Vehicle::all();
        $flights = Flight::all();
        $activities = Activity::all();
        $extras = Extra::all();

        // Cost sheet
        $costSheet = app(CostSheetService::class)->generate($itinerary);
        $publicPreviewUrl = URL::temporarySignedRoute(
            'itineraries.public-preview',
            now()->addDays(30),
            ['itinerary' => $itinerary->id]
        );
        $permanentPreviewUrl = $itinerary->public_share_token
            ? url('/itineraries/preview/' . $itinerary->public_share_token)
            : null;

        return view('pages.itinerary-show', compact(
            'itinerary', 'hotelRates', 'destinationFees', 'vehicles',
            'flights', 'activities', 'extras', 'costSheet', 'publicPreviewUrl', 'permanentPreviewUrl'
        ));
    }

    public function showItineraryBuilder(Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $companyId = $itinerary->company_id;

        $destinations = Destination::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $hotels = Hotel::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'location_id']);

        $hotelRates = HotelRate::query()
            ->with(['mealPlan:id,name'])
            ->where('company_id', $companyId)
            ->get(['id', 'hotel_id', 'meal_plan_id', 'season', 'start_date', 'end_date', 'price_per_person']);

        $flightProviders = FlightProvider::query()
            ->where('company_id', $companyId)
            ->with([
                'routes.originDestination:id,name',
                'routes.arrivalDestination:id,name',
                'rateTypes:id,flight_provider_id,name',
            ])
            ->get(['id', 'name', 'company_id']);

        $transportProviders = TransportProvider::query()
            ->where('company_id', $companyId)
            ->with([
                'transferRoutes.originDestination:id,name',
                'transferRoutes.arrivalDestination:id,name',
                'vehicleTypes:id,transport_provider_id,name',
            ])
            ->get(['id', 'name', 'company_id']);

        $activities = Activity::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'price_per_person']);

        $extras = Extra::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'price']);

        $packages = Package::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'destination_id', 'nights', 'price_mode', 'base_price', 'markup_percentage', 'discount_mode', 'discount_value', 'currency']);

        $costSheet = app(CostSheetService::class)->generate($itinerary);

        return view('pages.itinerary-builder', [
            'itinerary' => $itinerary->load('days.items'),
            'builderState' => $itinerary->builder_state ?? [],
            'destinations' => $destinations,
            'hotels' => $hotels,
            'hotelRates' => $hotelRates,
            'flightProviders' => $flightProviders,
            'transportProviders' => $transportProviders,
            'activities' => $activities,
            'extras' => $extras,
            'packages' => $packages,
            'costSheet' => $costSheet,
        ]);
    }

    public function safariCalendar(Request $request)
    {
        $itineraries = $this->scopedQuery(Itinerary::class, true)
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = strtolower((string) $request->query('status'));
                $query->whereJsonContains('builder_state->save->status', $status);
            })
            ->when($request->filled('from'), function ($query) use ($request) {
                $query->whereDate('start_date', '>=', $request->query('from'));
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                $query->whereDate('end_date', '<=', $request->query('to'));
            })
            ->orderBy('start_date')
            ->limit(200)
            ->get(['id', 'client_name', 'start_date', 'end_date', 'builder_state']);

        return view('pages.safari-calendar', [
            'calendarItineraries' => $itineraries,
            'filters' => $request->only(['status', 'from', 'to']),
        ]);
    }

    public function saveItineraryBuilderState(Request $request, Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $data = $request->validate([
            'builder_state' => 'required|array',
        ]);

        $itinerary->update([
            'builder_state' => $data['builder_state'],
        ]);

        return response()->json([
            'message' => 'Builder state saved.',
            'builder_state' => $itinerary->builder_state,
        ]);
    }

    public function rescheduleItinerary(Request $request, Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $data = $request->validate([
            'start_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($itinerary, $data): void {
            $start = \Carbon\Carbon::parse($data['start_date']);
            $duration = max(1, (int) $itinerary->total_days);
            $end = $start->copy()->addDays($duration - 1);

            $itinerary->update([
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'total_days' => $duration,
            ]);

            $days = $itinerary->days()->orderBy('day_number')->get();
            $currentCount = $days->count();

            if ($currentCount < $duration) {
                for ($i = $currentCount; $i < $duration; $i++) {
                    $itinerary->days()->create([
                        'day_number' => $i + 1,
                        'date' => $start->copy()->addDays($i)->toDateString(),
                    ]);
                }
                $days = $itinerary->days()->orderBy('day_number')->get();
            }

            if ($currentCount > $duration) {
                $itinerary->days()->where('day_number', '>', $duration)->delete();
                $days = $itinerary->days()->orderBy('day_number')->get();
            }

            foreach ($days as $index => $day) {
                $day->update([
                    'day_number' => $index + 1,
                    'date' => $start->copy()->addDays($index)->toDateString(),
                ]);
            }
        });

        app(ItineraryService::class)->recalculate($itinerary);

        return response()->json([
            'message' => 'Itinerary rescheduled.',
            'itinerary' => $itinerary->fresh(['days']),
        ]);
    }

    public function quoteItineraryService(Request $request, Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $data = $request->validate([
            'service_type' => 'required|in:accommodation,flight,transfer,transport,park_fee,package,extra',
            'payload' => 'required|array',
            'globals' => 'sometimes|array',
            'pricing_rules' => 'sometimes|array',
            'existing_services' => 'sometimes|array',
        ]);

        try {
            $result = app(SafariPricingBrainService::class)->quote(
                $itinerary,
                $data['service_type'],
                $data['payload'],
                $data['globals'] ?? [],
                $data['pricing_rules'] ?? [],
                $data['existing_services'] ?? []
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $service = $result['service'];

        return response()->json([
            'service_type' => $service['type'],
            'base_total' => $service['service_total'],
            'base_before_rules' => $service['base_total'],
            'markup_amount' => $service['markup_amount'],
            'discount_amount' => $service['discount_amount'],
            'label' => $service['label'],
            'breakdown' => $service['breakdown'],
            'item' => $service['item'],
            'combined' => $result['combined'],
        ]);
    }

    public function publicPreview(Request $request, Itinerary $itinerary)
    {
        $itinerary->load('days.items');
        $company = Company::find($itinerary->company_id);
        $costSheet = app(CostSheetService::class)->generate($itinerary);

        $previewDays = $itinerary->days->map(function ($day) {
            $items = $day->items->map(function ($item) {
                return [
                    'type' => $item->type,
                    'type_label' => $this->itineraryItemTypeLabel($item->type),
                    'label' => $this->itineraryItemLabel($item),
                    'quantity' => (int) $item->quantity,
                    'image_path' => $this->itineraryItemImagePath($item),
                ];
            })->values();

            return [
                'day_number' => $day->day_number,
                'date' => $day->date,
                'items' => $items,
            ];
        })->values();

        $isPermanentLink = false;

        return view('pages.itinerary-preview-public', compact('itinerary', 'company', 'costSheet', 'previewDays', 'isPermanentLink'));
    }

    public function publicPreviewByToken(string $token)
    {
        $itinerary = Itinerary::where('public_share_token', $token)->firstOrFail();
        $itinerary->load('days.items');
        $company = Company::find($itinerary->company_id);
        $costSheet = app(CostSheetService::class)->generate($itinerary);

        $previewDays = $itinerary->days->map(function ($day) {
            $items = $day->items->map(function ($item) {
                return [
                    'type' => $item->type,
                    'type_label' => $this->itineraryItemTypeLabel($item->type),
                    'label' => $this->itineraryItemLabel($item),
                    'quantity' => (int) $item->quantity,
                    'image_path' => $this->itineraryItemImagePath($item),
                ];
            })->values();

            return [
                'day_number' => $day->day_number,
                'date' => $day->date,
                'items' => $items,
            ];
        })->values();

        $isPermanentLink = true;

        return view('pages.itinerary-preview-public', compact('itinerary', 'company', 'costSheet', 'previewDays', 'isPermanentLink'));
    }

    public function regenerateShareToken(Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $itinerary->public_share_token = Str::random(48);
        $itinerary->save();

        return back()->with('success', 'Permanent preview link generated. Previous permanent link is now invalid.');
    }

    public function revokeShareToken(Itinerary $itinerary)
    {
        if (!$this->isSuperAdmin() && $itinerary->company_id !== $this->companyId()) {
            abort(403);
        }

        $itinerary->public_share_token = null;
        $itinerary->save();

        return back()->with('success', 'Permanent preview link revoked.');
    }

    private function itineraryItemTypeLabel(string $type): string
    {
        return match ($type) {
            'hotel' => 'Accommodation',
            'transport' => 'Transport',
            'park_fee' => 'Destination Fee',
            'flight' => 'Flight',
            'activity' => 'Activity',
            'extra' => 'Extra',
            default => ucfirst($type),
        };
    }

    private function itineraryItemLabel(ItineraryItem $item): string
    {
        $ref = $item->reference();

        return match ($item->type) {
            'hotel' => $ref ? ($ref->hotel?->name . ' / ' . $ref->roomType?->type . ' / ' . $ref->mealPlan?->name) : 'Accommodation',
            'transport' => $item->reference_source === 'transport_transfer_rate'
                ? ($ref ? (($ref->route?->originDestination?->name ?? '-') . ' to ' . ($ref->route?->arrivalDestination?->name ?? '-')) : 'Transfer')
                : ($ref ? ($ref->name . ' (' . $ref->capacity . ' pax)') : 'Transport'),
            'park_fee' => $ref ? ($ref->destination?->name . ' - ' . $ref->fee_type) : 'Destination Fee',
            'flight' => $item->reference_source === 'scheduled_flight'
                ? ($ref ? (($ref->route?->originDestination?->name ?? '-') . ' to ' . ($ref->route?->arrivalDestination?->name ?? '-') . ' / ' . ($ref->flight_number ?? 'Scheduled')) : 'Flight')
                : ($ref ? ($ref->name . ' (' . $ref->origin . ' to ' . $ref->destination . ')') : 'Flight'),
            'activity' => $ref?->name ?? 'Activity',
            'extra' => in_array($item->reference_source, ['manual_package', 'package'], true)
                ? (data_get($item->meta, 'label', 'Manual Package'))
                : ($ref?->name ?? 'Extra'),
            default => ucfirst($item->type),
        };
    }

    private function itineraryItemImagePath(ItineraryItem $item): ?string
    {
        if ($item->type === 'hotel') {
            $rate = HotelRate::with('hotel.accommodationMedia')->find($item->reference_id);
            if (!$rate || !$rate->hotel) {
                return null;
            }

            $cover = $rate->hotel->accommodationMedia->firstWhere('is_cover', true)
                ?? $rate->hotel->accommodationMedia->first();

            return $cover?->file_path;
        }

        if ($item->type === 'park_fee') {
            $fee = DestinationFee::with('destination.media')->find($item->reference_id);
            if (!$fee || !$fee->destination) {
                return null;
            }

            $cover = $fee->destination->media->firstWhere('is_cover', true)
                ?? $fee->destination->media->first();

            return $cover?->file_path;
        }

        return null;
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
            'reference_source' => 'nullable|string|max:60',
            'meta' => 'nullable|array',
        ]);

        $data['price'] = 0; // Selling price determined by itinerary-level markup

        // Verify the day belongs to this itinerary
        $day = $itinerary->days()->where('id', $data['itinerary_day_id'])->firstOrFail();

        $item = new ItineraryItem($data);

        // Calculate cost from master data (or keep manual cost if provided in meta)
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
        if (!$this->isSuperAdmin()) {
            abort(403);
        }

        $users = User::with('company')
            ->whereNull('company_id')
            ->whereIn('role', ['super_admin', 'admin'])
            ->orderBy('name')
            ->get();

        $companies = Company::orderBy('name')->get();

        return view('pages.users', compact('users', 'companies'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,admin,staff,hotel',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        $data['password'] = Hash::make($data['password']);

        if (!$this->isSuperAdmin()) {
            $data['company_id'] = $this->companyId();
            $data['role'] = 'staff';
        } else {
            $companyId = $data['company_id'] ?? null;

            if ($companyId && $data['role'] === 'super_admin') {
                return back()->with('error', 'Super admins cannot be assigned to a company.');
            }

            if (!$companyId && in_array($data['role'], ['staff', 'hotel'], true)) {
                return back()->with('error', 'Staff and hotel users must belong to a company.');
            }
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
