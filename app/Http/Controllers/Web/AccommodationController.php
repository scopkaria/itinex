<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MasterData\AccommodationBackupRate;
use App\Models\MasterData\AccommodationActivityModel;
use App\Models\MasterData\AccommodationCancellationPolicy;
use App\Models\MasterData\AccommodationChildPolicy;
use App\Models\MasterData\AccommodationExtraFee;
use App\Models\MasterData\AccommodationHolidaySupplement;
use App\Models\MasterData\AccommodationMedia;
use App\Models\MasterData\AccommodationPaymentPolicy;
use App\Models\MasterData\AccommodationRateType;
use App\Models\MasterData\AccommodationRateYear;
use App\Models\MasterData\AccommodationRoomRate;
use App\Models\MasterData\AccommodationSeason;
use App\Models\MasterData\AccommodationTourLeaderDiscount;
use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\MealPlan;
use App\Models\MasterData\RoomCategory;
use App\Models\MasterData\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccommodationController extends Controller
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
        if ($this->isSuperAdmin()) {
            return (int) $request->input('company_id');
        }
        return $this->companyId();
    }

    private function companyRules(): array
    {
        return $this->isSuperAdmin()
            ? ['company_id' => 'required|exists:companies,id']
            : [];
    }

    private function companiesForForm(): \Illuminate\Support\Collection
    {
        return $this->isSuperAdmin()
            ? Company::orderBy('name')->get()
            : collect();
    }

    private function authorize(Hotel $hotel): void
    {
        // Master data is accessible to all authenticated users
    }

    // ─── List ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = $this->scopedQuery(Hotel::class)
            ->with('location')
            ->withCount(['roomCategories', 'rateYears']);

        // Search by name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by chain
        if ($chain = $request->input('chain')) {
            $query->where('chain', $chain);
        }

        // Filter by location (destination)
        if ($location = $request->input('location')) {
            $query->where('location_id', $location);
        }

        // Alphabetical letter filter
        if ($letter = $request->input('letter')) {
            $query->where('name', 'like', $letter . '%');
        }

        $accommodations = $query->orderBy('name')->paginate(25)->withQueryString();

        // Get distinct chains for filter dropdown
        $chainsQuery = $this->scopedQuery(Hotel::class)->whereNotNull('chain')->distinct()->pluck('chain')->sort()->values();

        $destinations = $this->scopedQuery(Destination::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();

        return view('pages.accommodations', compact('accommodations', 'destinations', 'companies', 'chainsQuery'));
    }

    // ─── Create / Store ────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:destinations,id',
            'category' => 'required|in:budget,midrange,luxury',
            'chain' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'vat_type' => 'nullable|in:inclusive,exclusive,exempt',
            'markup' => 'nullable|numeric|min:0',
            ...$this->companyRules(),
        ]);
        $data['company_id'] = $this->resolveCompanyId($request);
        $data['slug'] = Str::slug($data['name']);
        $hotel = Hotel::create($data);
        return redirect('/accommodations/' . $hotel->id . '/edit')->with('success', 'Accommodation created.');
    }

    // ─── Edit (Detail page with tabs) ──────────────────────────

    public function edit(Hotel $hotel)
    {
        $this->authorize($hotel);
        $hotel->load([
            'location', 'roomCategories', 'roomTypes',
            'accommodationMedia', 'rateYears.seasons', 'rateTypes',
            'extraFees', 'holidaySupplements', 'accommodationActivities',
            'childPolicies', 'paymentPolicies', 'cancellationPolicies',
            'tourLeaderDiscounts', 'backupRates',
        ]);

        $activeYear = $hotel->rateYears->where('is_active', true)->first();
        $roomRates = $activeYear
            ? AccommodationRoomRate::where('rate_year_id', $activeYear->id)->with(['season', 'roomCategory', 'roomType', 'mealPlan', 'rateType'])->get()
            : collect();

        $destinations = $this->scopedQuery(Destination::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        $mealPlans = MealPlan::orderBy('name')->get();

        return view('pages.accommodation-form', [
            'hotel' => $hotel,
            'destinations' => $destinations,
            'companies' => $companies,
            'activeYear' => $activeYear,
            'roomRates' => $roomRates,
            'mealPlans' => $mealPlans,
            'mode' => 'edit',
        ]);
    }

    // ─── Update basic info ─────────────────────────────────────

    public function update(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:destinations,id',
            'category' => 'required|in:budget,midrange,luxury',
            'chain' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'vat_type' => 'nullable|in:inclusive,exclusive,exempt',
            'markup' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $hotel->update($data);
        return back()->with('success', 'Accommodation updated.');
    }

    public function delete(Hotel $hotel)
    {
        $this->authorize($hotel);
        $hotel->delete();
        return redirect('/accommodations')->with('success', 'Accommodation deleted.');
    }

    // ─── Room Categories ───────────────────────────────────────

    public function storeRoomCategory(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $hotel->roomCategories()->create($data);
        return back()->with('success', 'Room category added.');
    }

    public function deleteRoomCategory(Hotel $hotel, RoomCategory $category)
    {
        $this->authorize($hotel);
        $category->delete();
        return back()->with('success', 'Room category deleted.');
    }

    // ─── Gallery / Media ───────────────────────────────────────

    public function uploadMedia(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $request->validate(['media' => 'required|image|max:5120']);
        $path = $request->file('media')->store('accommodations/' . $hotel->id, 'public');
        $hotel->accommodationMedia()->create([
            'file_path' => $path,
            'sort_order' => $hotel->accommodationMedia()->count(),
        ]);
        return back()->with('success', 'Image uploaded.');
    }

    public function deleteMedia(Hotel $hotel, AccommodationMedia $media)
    {
        $this->authorize($hotel);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($media->file_path);
        $media->delete();
        return back()->with('success', 'Image deleted.');
    }

    public function setCoverMedia(Hotel $hotel, AccommodationMedia $media)
    {
        $this->authorize($hotel);
        $hotel->accommodationMedia()->update(['is_cover' => false]);
        $media->update(['is_cover' => true]);
        return back()->with('success', 'Cover image set.');
    }

    public function reorderMedia(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $order = $request->validate(['order' => 'required|array'])['order'];
        foreach ($order as $i => $id) {
            AccommodationMedia::where('id', $id)->where('hotel_id', $hotel->id)->update(['sort_order' => $i]);
        }
        return response()->json(['ok' => true]);
    }

    // ─── Rate Years ────────────────────────────────────────────

    public function storeRateYear(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate(['year' => 'required|integer|min:2020|max:2050']);
        $hotel->rateYears()->create($data);
        return back()->with('success', 'Rate year added.');
    }

    public function activateRateYear(Hotel $hotel, AccommodationRateYear $year)
    {
        $this->authorize($hotel);
        $hotel->rateYears()->update(['is_active' => false]);
        $year->update(['is_active' => true]);
        return back()->with('success', 'Rate year activated.');
    }

    public function cloneRateYear(Request $request, Hotel $hotel, AccommodationRateYear $year)
    {
        $this->authorize($hotel);
        $data = $request->validate(['target_year' => 'required|integer|min:2020|max:2050']);
        $newYear = $hotel->rateYears()->create(['year' => $data['target_year']]);

        foreach ($year->seasons as $season) {
            $newSeason = $newYear->seasons()->create($season->only(['name', 'start_date', 'end_date']));
            $rates = AccommodationRoomRate::where('season_id', $season->id)->get();
            foreach ($rates as $rate) {
                $newRate = $rate->replicate(['id', 'created_at', 'updated_at']);
                $newRate->rate_year_id = $newYear->id;
                $newRate->season_id = $newSeason->id;
                $newRate->save();
            }
        }

        return back()->with('success', "Rates cloned to {$data['target_year']}.");
    }

    // ─── Seasons ───────────────────────────────────────────────

    public function storeSeason(Request $request, Hotel $hotel, AccommodationRateYear $year)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $year->seasons()->create($data);
        return back()->with('success', 'Season added.');
    }

    public function deleteSeason(Hotel $hotel, AccommodationSeason $season)
    {
        $this->authorize($hotel);
        $season->delete();
        return back()->with('success', 'Season deleted.');
    }

    // ─── Room Rates ────────────────────────────────────────────

    public function storeRoomRate(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'rate_year_id' => 'required|exists:accommodation_rate_years,id',
            'season_id' => 'required|exists:accommodation_seasons,id',
            'room_category_id' => 'nullable|exists:room_categories,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'meal_plan_id' => 'nullable|exists:meal_plans,id',
            'rate_type_id' => 'nullable|exists:accommodation_rate_types,id',
            'adult_rate' => 'required|numeric|min:0',
            'child_rate' => 'nullable|numeric|min:0',
            'infant_rate' => 'nullable|numeric|min:0',
            'single_supplement' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);
        $data['hotel_id'] = $hotel->id;
        AccommodationRoomRate::create($data);
        return back()->with('success', 'Room rate added.');
    }

    public function deleteRoomRate(Hotel $hotel, AccommodationRoomRate $rate)
    {
        $this->authorize($hotel);
        $rate->delete();
        return back()->with('success', 'Room rate deleted.');
    }

    // ─── Rate Types ────────────────────────────────────────────

    public function storeRateType(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $hotel->rateTypes()->create($data);
        return back()->with('success', 'Rate type added.');
    }

    public function deleteRateType(Hotel $hotel, AccommodationRateType $type)
    {
        $this->authorize($hotel);
        $type->delete();
        return back()->with('success', 'Rate type deleted.');
    }

    // ─── Extra Fees ────────────────────────────────────────────

    public function storeExtraFee(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'rate_year_id' => 'nullable|exists:accommodation_rate_years,id',
            'name' => 'required|string|max:255',
            'fee_type' => 'required|in:per_person,per_room,flat',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $hotel->extraFees()->create($data);
        return back()->with('success', 'Extra fee added.');
    }

    public function deleteExtraFee(Hotel $hotel, AccommodationExtraFee $fee)
    {
        $this->authorize($hotel);
        $fee->delete();
        return back()->with('success', 'Extra fee deleted.');
    }

    // ─── Holiday Supplements ───────────────────────────────────

    public function storeHolidaySupplement(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'rate_year_id' => 'nullable|exists:accommodation_rate_years,id',
            'holiday_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'supplement_amount' => 'required|numeric|min:0',
            'apply_to' => 'required|in:per_person,per_room',
        ]);
        $hotel->holidaySupplements()->create($data);
        return back()->with('success', 'Holiday supplement added.');
    }

    public function deleteHolidaySupplement(Hotel $hotel, AccommodationHolidaySupplement $supplement)
    {
        $this->authorize($hotel);
        $supplement->delete();
        return back()->with('success', 'Holiday supplement deleted.');
    }

    // ─── Activities ────────────────────────────────────────────

    public function storeActivity(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_person' => 'required|numeric|min:0',
        ]);
        $hotel->accommodationActivities()->create($data);
        return back()->with('success', 'Activity added.');
    }

    public function deleteActivity(Hotel $hotel, AccommodationActivityModel $activity)
    {
        $this->authorize($hotel);
        $activity->delete();
        return back()->with('success', 'Activity deleted.');
    }

    // ─── Child Policies ────────────────────────────────────────

    public function storeChildPolicy(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'min_age' => 'required|integer|min:0',
            'max_age' => 'required|integer|min:0',
            'policy_type' => 'required|in:percentage,fixed,free',
            'value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $hotel->childPolicies()->create($data);
        return back()->with('success', 'Child policy added.');
    }

    public function deleteChildPolicy(Hotel $hotel, AccommodationChildPolicy $policy)
    {
        $this->authorize($hotel);
        $policy->delete();
        return back()->with('success', 'Child policy deleted.');
    }

    // ─── Payment Policies ──────────────────────────────────────

    public function storePaymentPolicy(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        $hotel->paymentPolicies()->create($data);
        return back()->with('success', 'Payment policy added.');
    }

    public function deletePaymentPolicy(Hotel $hotel, AccommodationPaymentPolicy $policy)
    {
        $this->authorize($hotel);
        $policy->delete();
        return back()->with('success', 'Payment policy deleted.');
    }

    // ─── Cancellation Policies ─────────────────────────────────

    public function storeCancellationPolicy(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'days_before' => 'required|integer|min:0',
            'penalty_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);
        $hotel->cancellationPolicies()->create($data);
        return back()->with('success', 'Cancellation policy added.');
    }

    public function deleteCancellationPolicy(Hotel $hotel, AccommodationCancellationPolicy $policy)
    {
        $this->authorize($hotel);
        $policy->delete();
        return back()->with('success', 'Cancellation policy deleted.');
    }

    // ─── Tour Leader Discounts ─────────────────────────────────

    public function storeTourLeaderDiscount(Request $request, Hotel $hotel)
    {
        $this->authorize($hotel);
        $data = $request->validate([
            'min_pax' => 'required|integer|min:1',
            'discount_type' => 'required|in:free,percentage,fixed',
            'value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $hotel->tourLeaderDiscounts()->create($data);
        return back()->with('success', 'Tour leader discount added.');
    }

    public function deleteTourLeaderDiscount(Hotel $hotel, AccommodationTourLeaderDiscount $discount)
    {
        $this->authorize($hotel);
        $discount->delete();
        return back()->with('success', 'Tour leader discount deleted.');
    }

    // ─── CSV Bulk Import ───────────────────────────────────────

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $companyId = $this->resolveCompanyId($request);
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // expects: name, chain, location

        if (!$header || count($header) < 3) {
            fclose($handle);
            return back()->with('error', 'CSV must have columns: name, chain, location');
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3 || empty(trim($row[0]))) {
                $skipped++;
                continue;
            }

            $name = trim($row[0]);
            $chain = trim($row[1]);
            $location = trim($row[2]);
            $slug = Str::slug($name);

            if (strtoupper($chain) === 'NA' || $chain === '' || strtoupper($chain) === 'NULL') {
                $chain = null;
            }

            // Find or create destination
            $dest = Destination::whereRaw('LOWER(name) = ?', [strtolower($location)])->first();
            if (!$dest) {
                $dest = Destination::create([
                    'company_id' => $companyId,
                    'name'       => ucwords(strtolower($location)),
                ]);
            }

            // Skip duplicates
            if (Hotel::where('slug', $slug)->exists()) {
                $skipped++;
                continue;
            }

            Hotel::create([
                'company_id'  => $companyId,
                'name'        => $name,
                'slug'        => $slug,
                'chain'       => $chain,
                'location_id' => $dest->id,
                'category'    => 'midrange',
                'is_active'   => true,
            ]);
            $imported++;
        }

        fclose($handle);
        return back()->with('success', "Imported {$imported} accommodations. Skipped {$skipped}.");
    }
}
