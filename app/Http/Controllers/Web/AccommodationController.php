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
use App\Models\User;
use App\Services\Pricing\RateAuditVersioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccommodationController extends Controller
{
    public function __construct(
        private readonly RateAuditVersioningService $rateAuditService,
    ) {
    }

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
        $query = $model::query();
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isHotel()) {
            if ($model === Hotel::class) {
                return $query->whereHas('owners', function ($ownerQuery) use ($user) {
                    $ownerQuery->where('users.id', $user->id);
                });
            }

            return $query->where('company_id', $this->companyId());
        }

        $query->where('company_id', $this->companyId());

        return $query;
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

    private function authorizeView(Hotel $hotel): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isHotel()) {
            $isAssigned = $hotel->owners()->where('users.id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized hotel access.');
            }
            return;
        }

        if ((int) $hotel->company_id !== (int) $this->companyId()) {
            abort(403, 'Unauthorized hotel access.');
        }
    }

    private function authorizeManage(Hotel $hotel): void
    {
        $this->authorizeView($hotel);

        if ($this->canManageAccommodation($hotel)) {
            return;
        }

        abort(403, 'You are not allowed to modify accommodation data for this property.');
    }

    private function canManageAccommodation(Hotel $hotel): bool
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isHotel()) {
            return $hotel->owners()->where('users.id', $user->id)->exists();
        }

        if ($user->isAdmin()) {
            return (int) $hotel->company_id === (int) $this->companyId()
                && (bool) ($user->company?->accommodation_company_edit_enabled ?? false);
        }

        return false;
    }

    private function canViewRawRates(Hotel $hotel): bool
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isHotel()) {
            return $hotel->owners()->where('users.id', $user->id)->exists();
        }

        if ($user->isAdmin()) {
            return (int) $hotel->company_id === (int) $this->companyId()
                && (bool) ($user->company?->accommodation_company_sto_edit_enabled ?? false);
        }

        return false;
    }

    private function canOverrideRates(): bool
    {
        return Auth::user()->isSuperAdmin();
    }

    private function buildRateUniquenessGuard(array $data): string
    {
        return implode(':', [
            (int) ($data['rate_year_id'] ?? 0),
            (int) ($data['season_id'] ?? 0),
            (int) ($data['room_type_id'] ?? 0),
            (int) ($data['meal_plan_id'] ?? 0),
            (int) ($data['rate_type_id'] ?? 0),
        ]);
    }

    private function deriveBaseRate(array $data): array
    {
        $source = 'manual';
        $baseRate = isset($data['adult_rate']) ? (float) $data['adult_rate'] : 0.0;

        if (array_key_exists('promotional_rate', $data) && $data['promotional_rate'] !== null && $data['promotional_rate'] !== '') {
            $baseRate = (float) $data['promotional_rate'];
            $source = 'promotional';
        } elseif (array_key_exists('contracted_rate', $data) && $data['contracted_rate'] !== null && $data['contracted_rate'] !== '') {
            $baseRate = (float) $data['contracted_rate'];
            $source = 'contracted';
        } elseif (array_key_exists('sto_rate_raw', $data) && $data['sto_rate_raw'] !== null && $data['sto_rate_raw'] !== '') {
            $baseRate = (float) $data['sto_rate_raw'];
            $source = 'sto';
        }

        return [$baseRate, $source];
    }

    private function applyHotelMarkup(float $baseRate, Hotel $hotel): float
    {
        $markupPct = (float) ($hotel->markup ?? 0);

        return round($baseRate * (1 + ($markupPct / 100)), 2);
    }

    // ─── List ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = $this->scopedQuery(Hotel::class)
            ->with(['location.countryRef', 'location.regionRef', 'accommodationMedia'])
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

        if ($countryId = $request->input('country_id')) {
            $query->whereHas('location', function ($locationQuery) use ($countryId) {
                $locationQuery->where('country_id', (int) $countryId);
            });
        }

        if ($regionId = $request->input('region_id')) {
            $query->whereHas('location', function ($locationQuery) use ($regionId) {
                $locationQuery->where('region_id', (int) $regionId);
            });
        }

        // Alphabetical letter filter
        if ($letter = $request->input('letter')) {
            $query->where('name', 'like', $letter . '%');
        }

        $accommodations = $query->orderBy('name')->paginate(25)->withQueryString();

        // Get distinct chains for filter dropdown
        $chainsQuery = $this->scopedQuery(Hotel::class)->whereNotNull('chain')->distinct()->pluck('chain')->sort()->values();

        $destinations = $this->scopedQuery(Destination::class)
            ->with(['countryRef', 'regionRef'])
            ->orderBy('name')
            ->get();
        $countries = $destinations->pluck('countryRef')->filter()->unique('id')->sortBy('name')->values();
        $regions = $destinations->pluck('regionRef')->filter()->unique('id')->sortBy('name')->values();
        $companies = $this->companiesForForm();

        return view('pages.accommodations', compact('accommodations', 'destinations', 'countries', 'regions', 'companies', 'chainsQuery'));
    }

    // ─── Create / Store ────────────────────────────────────────

    public function store(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Only super admins can create accommodations.');
        }

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
        return redirect('/accommodations/' . $hotel->id . '/manage')->with('success', 'Accommodation created.');
    }

    // ─── View / Manage pages ───────────────────────────────────

    public function show(Hotel $hotel)
    {
        $this->authorizeView($hotel);
        $hotel->load([
            'location', 'roomCategories', 'roomTypes',
            'accommodationMedia', 'rateYears.seasons', 'rateTypes',
            'extraFees', 'holidaySupplements', 'accommodationActivities',
            'childPolicies', 'paymentPolicies', 'cancellationPolicies',
            'tourLeaderDiscounts', 'backupRates', 'owners',
        ]);

        $activeYear = $hotel->rateYears->where('is_active', true)->first();
        $roomRates = $activeYear
            ? AccommodationRoomRate::where('rate_year_id', $activeYear->id)->with(['season', 'roomCategory', 'roomType', 'mealPlan', 'rateType'])->get()
            : collect();

        if (!$this->canViewRawRates($hotel)) {
            $roomRates->each(function ($rate) {
                $rate->sto_rate_raw = null;
                if (in_array($rate->visibility_mode, ['computed', 'computed_only'], true)) {
                    $rate->adult_rate = $rate->derived_rate ?? $rate->adult_rate;
                }
            });
        }

        $destinations = $this->scopedQuery(Destination::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        $mealPlans = MealPlan::orderBy('name')->get();
        $ownerCandidates = User::query()
            ->where('role', User::ROLE_HOTEL)
            ->where('company_id', $hotel->company_id)
            ->orderBy('name')
            ->get();

        return view('pages.accommodation-view', [
            'hotel' => $hotel,
            'destinations' => $destinations,
            'companies' => $companies,
            'activeYear' => $activeYear,
            'roomRates' => $roomRates,
            'mealPlans' => $mealPlans,
            'ownerCandidates' => $ownerCandidates,
            'canManageAccommodation' => $this->canManageAccommodation($hotel),
            'canViewRawRates' => $this->canViewRawRates($hotel),
        ]);
    }

    public function manage(Hotel $hotel)
    {
        $this->authorizeView($hotel);
        $hotel->load([
            'location', 'roomCategories', 'roomTypes',
            'accommodationMedia', 'rateYears.seasons', 'rateTypes',
            'extraFees', 'holidaySupplements', 'accommodationActivities',
            'childPolicies', 'paymentPolicies', 'cancellationPolicies',
            'tourLeaderDiscounts', 'backupRates', 'owners',
        ]);

        $activeYear = $hotel->rateYears->where('is_active', true)->first();
        $roomRates = $activeYear
            ? AccommodationRoomRate::where('rate_year_id', $activeYear->id)->with(['season', 'roomCategory', 'roomType', 'mealPlan', 'rateType'])->get()
            : collect();

        if (!$this->canViewRawRates($hotel)) {
            $roomRates->each(function ($rate) {
                $rate->sto_rate_raw = null;
                if (in_array($rate->visibility_mode, ['computed', 'computed_only'], true)) {
                    $rate->adult_rate = $rate->derived_rate ?? $rate->adult_rate;
                }
            });
        }

        $destinations = $this->scopedQuery(Destination::class)->orderBy('name')->get();
        $companies = $this->companiesForForm();
        $mealPlans = MealPlan::orderBy('name')->get();
        $ownerCandidates = User::query()
            ->where('role', User::ROLE_HOTEL)
            ->where('company_id', $hotel->company_id)
            ->orderBy('name')
            ->get();

        return view('pages.accommodation-form', [
            'hotel' => $hotel,
            'destinations' => $destinations,
            'companies' => $companies,
            'activeYear' => $activeYear,
            'roomRates' => $roomRates,
            'mealPlans' => $mealPlans,
            'ownerCandidates' => $ownerCandidates,
            'canManageAccommodation' => $this->canManageAccommodation($hotel),
            'canViewRawRates' => $this->canViewRawRates($hotel),
            'canOverrideRates' => $this->canOverrideRates(),
            'mode' => 'manage',
        ]);
    }

    public function edit(Hotel $hotel)
    {
        return $this->manage($hotel);
    }

    // ─── Update basic info ─────────────────────────────────────

    public function update(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
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
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only super admins can delete accommodations.');
        }

        $hotel->delete();
        return redirect('/accommodations')->with('success', 'Accommodation deleted.');
    }

    public function syncOwners(Request $request, Hotel $hotel)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only super admins can assign accommodation owners.');
        }

        $data = $request->validate([
            'owner_user_ids' => 'nullable|array',
            'owner_user_ids.*' => 'integer|exists:users,id',
        ]);

        $ownerIds = collect($data['owner_user_ids'] ?? [])->map(fn($id) => (int) $id)->values();

        $validOwnerIds = User::query()
            ->whereIn('id', $ownerIds)
            ->where('role', User::ROLE_HOTEL)
            ->where('company_id', $hotel->company_id)
            ->pluck('id')
            ->all();

        $hotel->owners()->sync($validOwnerIds);

        return back()->with('success', 'Accommodation owners updated.');
    }

    // ─── Room Categories ───────────────────────────────────────

    public function storeRoomCategory(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
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
        $this->authorizeManage($hotel);
        $category->delete();
        return back()->with('success', 'Room category deleted.');
    }

    // ─── Gallery / Media ───────────────────────────────────────

    public function uploadMedia(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);

        if ($request->hasFile('images')) {
            $request->validate([
                'images' => 'required|array|max:20',
                'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            ]);

            $sortOrder = $hotel->accommodationMedia()->count();
            foreach ($request->file('images') as $file) {
                $path = $file->store('accommodations/' . $hotel->id, 'public');
                $hotel->accommodationMedia()->create([
                    'file_path' => $path,
                    'sort_order' => $sortOrder++,
                    'is_cover' => $hotel->accommodationMedia()->count() === 0,
                ]);
            }
        } else {
            $request->validate(['media' => 'required|image|max:5120']);
            $path = $request->file('media')->store('accommodations/' . $hotel->id, 'public');
            $hotel->accommodationMedia()->create([
                'file_path' => $path,
                'sort_order' => $hotel->accommodationMedia()->count(),
                'is_cover' => $hotel->accommodationMedia()->count() === 0,
            ]);
        }

        return back()->with('success', 'Image uploaded.');
    }

    public function deleteMedia(Hotel $hotel, AccommodationMedia $media)
    {
        $this->authorizeManage($hotel);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($media->file_path);
        $media->delete();
        return back()->with('success', 'Image deleted.');
    }

    public function setCoverMedia(Hotel $hotel, AccommodationMedia $media)
    {
        $this->authorizeManage($hotel);
        $hotel->accommodationMedia()->update(['is_cover' => false]);
        $media->update(['is_cover' => true]);
        return back()->with('success', 'Cover image set.');
    }

    public function reorderMedia(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $order = $request->validate(['order' => 'required|array'])['order'];
        foreach ($order as $i => $id) {
            AccommodationMedia::where('id', $id)->where('hotel_id', $hotel->id)->update(['sort_order' => $i]);
        }
        return response()->json(['ok' => true]);
    }

    // ─── Rate Years ────────────────────────────────────────────

    public function storeRateYear(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate(['year' => 'required|integer|min:2020|max:2050']);
        $hotel->rateYears()->create($data);
        return back()->with('success', 'Rate year added.');
    }

    public function activateRateYear(Hotel $hotel, AccommodationRateYear $year)
    {
        $this->authorizeManage($hotel);
        $hotel->rateYears()->update(['is_active' => false]);
        $year->update(['is_active' => true]);
        return back()->with('success', 'Rate year activated.');
    }

    public function cloneRateYear(Request $request, Hotel $hotel, AccommodationRateYear $year)
    {
        $this->authorizeManage($hotel);
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
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location_id' => 'nullable|exists:destinations,id',
        ]);
        $year->seasons()->create($data);
        return back()->with('success', 'Season added.');
    }

    public function deleteSeason(Hotel $hotel, AccommodationSeason $season)
    {
        $this->authorizeManage($hotel);
        $season->delete();
        return back()->with('success', 'Season deleted.');
    }

    // ─── Room Rates ────────────────────────────────────────────

    public function storeRoomRate(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $canWriteRaw = $this->canViewRawRates($hotel);

        $data = $request->validate([
            'rate_year_id' => 'required|exists:accommodation_rate_years,id',
            'season_id' => 'required|exists:accommodation_seasons,id',
            'room_category_id' => 'nullable|exists:room_categories,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'meal_plan_id' => 'nullable|exists:meal_plans,id',
            'rate_type_id' => 'nullable|exists:accommodation_rate_types,id',
            'rate_kind' => 'nullable|in:sto,contract,promo',
            'adult_rate' => 'nullable|numeric|min:0',
            'sto_rate_raw' => ($canWriteRaw ? 'nullable' : 'prohibited') . '|numeric|min:0',
            'contracted_rate' => 'nullable|numeric|min:0',
            'promotional_rate' => 'nullable|numeric|min:0',
            'markup_percent' => 'nullable|numeric|min:0',
            'markup_fixed' => 'nullable|numeric|min:0',
            'child_rate' => 'nullable|numeric|min:0',
            'infant_rate' => 'nullable|numeric|min:0',
            'single_supplement' => 'nullable|numeric|min:0',
            'per_person_sharing_double' => 'nullable|numeric|min:0',
            'per_person_sharing_twin' => 'nullable|numeric|min:0',
            'triple_adjustment' => 'nullable|numeric',
            'currency' => 'nullable|string|size:3',
            'visibility_mode' => 'required|in:private,computed,computed_only',
            'is_override' => 'nullable|boolean',
        ]);

        [$baseRate, $source] = $this->deriveBaseRate($data);
        $rateType = isset($data['rate_type_id'])
            ? AccommodationRateType::query()->find($data['rate_type_id'])
            : null;

        $markupPercent = (float) ($data['markup_percent'] ?? $rateType?->markup_percent ?? $hotel->markup ?? 0);
        $markupFixed = (float) ($data['markup_fixed'] ?? $rateType?->markup_fixed ?? 0);
        $derivedRate = round(($baseRate * (1 + ($markupPercent / 100))) + $markupFixed, 2);

        $data['hotel_id'] = $hotel->id;
        $data['rate_kind'] = $data['rate_kind'] ?? 'sto';
        $data['markup_percent'] = $markupPercent;
        $data['markup_fixed'] = $markupFixed;
        $data['per_person_sharing_double'] = (float) ($data['per_person_sharing_double'] ?? $derivedRate);
        $data['per_person_sharing_twin'] = (float) ($data['per_person_sharing_twin'] ?? $derivedRate);
        $data['triple_adjustment'] = (float) ($data['triple_adjustment'] ?? 0);
        $data['rate_source'] = $source;
        $data['derived_rate'] = $derivedRate;
        $data['adult_rate'] = $derivedRate;
        $data['is_override'] = $this->canOverrideRates() && $request->boolean('is_override', false);
        $data['sto_rate_raw'] = $canWriteRaw && array_key_exists('sto_rate_raw', $data) && $data['sto_rate_raw'] !== null
            ? (string) $data['sto_rate_raw']
            : null;

        $guard = $this->buildRateUniquenessGuard($data);

        $existing = AccommodationRoomRate::query()
            ->where('hotel_id', $hotel->id)
            ->where('rate_uniqueness_guard', $guard)
            ->first();

        $rate = AccommodationRoomRate::updateOrCreate(
            [
                'hotel_id' => $hotel->id,
                'rate_uniqueness_guard' => $guard,
            ],
            array_merge($data, ['rate_uniqueness_guard' => $guard])
        );

        $this->rateAuditService->record(
            module: 'accommodation',
            companyId: (int) $hotel->company_id,
            providerId: (int) $hotel->id,
            providerType: Hotel::class,
            entityType: 'accommodation_room_rate',
            entityId: (int) $rate->id,
            action: $existing ? 'updated' : 'created',
            beforeState: $existing?->toArray(),
            afterState: $rate->toArray(),
            changedBy: Auth::id(),
            source: 'web'
        );

        return back()->with('success', 'Room rate added.');
    }

    public function deleteRoomRate(Hotel $hotel, AccommodationRoomRate $rate)
    {
        $this->authorizeManage($hotel);
        $before = $rate->toArray();
        $rate->delete();

        $this->rateAuditService->record(
            module: 'accommodation',
            companyId: (int) $hotel->company_id,
            providerId: (int) $hotel->id,
            providerType: Hotel::class,
            entityType: 'accommodation_room_rate',
            entityId: (int) ($before['id'] ?? 0),
            action: 'deleted',
            beforeState: $before,
            afterState: null,
            changedBy: Auth::id(),
            source: 'web'
        );

        return back()->with('success', 'Room rate deleted.');
    }

    // ─── Rate Types ────────────────────────────────────────────

    public function storeRateType(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'markup_percent' => 'nullable|numeric|min:0',
            'markup_fixed' => 'nullable|numeric|min:0',
        ]);
        $hotel->rateTypes()->create($data);
        return back()->with('success', 'Rate type added.');
    }

    public function syncRoomTypes(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);

        $selected = collect($request->input('room_types', []))
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values();

        $preset = [
            'single' => ['label' => 'Single', 'max_adults' => 1],
            'double' => ['label' => 'Double', 'max_adults' => 2],
            'twin' => ['label' => 'Twin', 'max_adults' => 2],
            'twin_single' => ['label' => 'Twin + Single', 'max_adults' => 3],
            'triple' => ['label' => 'Triple', 'max_adults' => 3],
            'quadruple' => ['label' => 'Quadruple', 'max_adults' => 4],
            'quintuple' => ['label' => 'Quintuple', 'max_adults' => 5],
            'family' => ['label' => 'Family', 'max_adults' => 6],
        ];

        RoomType::where('hotel_id', $hotel->id)
            ->whereNotIn('type', $selected->all())
            ->delete();

        foreach ($selected as $type) {
            if (!isset($preset[$type])) {
                continue;
            }

            RoomType::updateOrCreate(
                ['hotel_id' => $hotel->id, 'type' => $type],
                [
                    'label' => $preset[$type]['label'],
                    'max_adults' => $preset[$type]['max_adults'],
                ]
            );
        }

        return back()->with('success', 'Room types updated.');
    }

    public function storeMealPlan(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);

        $data = $request->validate([
            'abbreviation' => 'required|string|max:10',
            'full_name' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_fr' => 'nullable|string',
        ]);

        MealPlan::updateOrCreate(
            ['name' => strtoupper($data['abbreviation'])],
            [
                'abbreviation' => strtoupper($data['abbreviation']),
                'full_name' => $data['full_name'],
                'description_i18n' => [
                    'en' => $data['description_en'] ?? $data['full_name'],
                    'fr' => $data['description_fr'] ?? null,
                ],
            ]
        );

        return back()->with('success', 'Meal plan saved.');
    }

    public function deleteRateType(Hotel $hotel, AccommodationRateType $type)
    {
        $this->authorizeManage($hotel);
        $type->delete();
        return back()->with('success', 'Rate type deleted.');
    }

    // ─── Extra Fees ────────────────────────────────────────────

    public function storeExtraFee(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'rate_year_id' => 'nullable|exists:accommodation_rate_years,id',
            'name' => 'required|string|max:255',
            'fee_type' => 'required|in:per_person,per_room,flat',
            'adult_rate' => 'nullable|numeric|min:0',
            'child_rate' => 'nullable|numeric|min:0',
            'resident_rate' => 'nullable|numeric|min:0',
            'non_resident_rate' => 'nullable|numeric|min:0',
            'apply_per' => 'nullable|in:person,vehicle,group',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $hotel->extraFees()->create($data);
        return back()->with('success', 'Extra fee added.');
    }

    public function updateExtraFee(Request $request, Hotel $hotel, AccommodationExtraFee $fee)
    {
        $this->authorizeManage($hotel);
        if ((int) $fee->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'fee_type' => 'required|in:per_person,per_room,flat',
            'adult_rate' => 'nullable|numeric|min:0',
            'child_rate' => 'nullable|numeric|min:0',
            'resident_rate' => 'nullable|numeric|min:0',
            'non_resident_rate' => 'nullable|numeric|min:0',
            'apply_per' => 'nullable|in:person,vehicle,group',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $fee->update($data);
        return back()->with('success', 'Extra fee updated.');
    }

    public function deleteExtraFee(Hotel $hotel, AccommodationExtraFee $fee)
    {
        $this->authorizeManage($hotel);
        $fee->delete();
        return back()->with('success', 'Extra fee deleted.');
    }

    // ─── Holiday Supplements ───────────────────────────────────

    public function storeHolidaySupplement(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'rate_year_id' => 'nullable|exists:accommodation_rate_years,id',
            'holiday_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'supplement_amount' => 'required|numeric|min:0',
            'apply_to' => 'required|in:per_person,per_room',
            'supplement_date' => 'nullable|date',
            'adult_rate' => 'nullable|numeric|min:0',
            'child_rate' => 'nullable|numeric|min:0',
            'room_type_id' => 'nullable|exists:room_types,id',
        ]);
        $hotel->holidaySupplements()->create($data);
        return back()->with('success', 'Holiday supplement added.');
    }

    public function updateHolidaySupplement(Request $request, Hotel $hotel, AccommodationHolidaySupplement $supplement)
    {
        $this->authorizeManage($hotel);
        if ((int) $supplement->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $data = $request->validate([
            'holiday_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'supplement_amount' => 'required|numeric|min:0',
            'apply_to' => 'required|in:per_person,per_room',
            'supplement_date' => 'nullable|date',
            'adult_rate' => 'nullable|numeric|min:0',
            'child_rate' => 'nullable|numeric|min:0',
            'room_type_id' => 'nullable|exists:room_types,id',
        ]);

        $supplement->update($data);
        return back()->with('success', 'Holiday supplement updated.');
    }

    public function deleteHolidaySupplement(Hotel $hotel, AccommodationHolidaySupplement $supplement)
    {
        $this->authorizeManage($hotel);
        $supplement->delete();
        return back()->with('success', 'Holiday supplement deleted.');
    }

    // ─── Activities ────────────────────────────────────────────

    public function storeActivity(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_person' => 'required|numeric|min:0',
            'rate_adult' => 'nullable|numeric|min:0',
            'rate_child' => 'nullable|numeric|min:0',
            'rate_guide' => 'nullable|numeric|min:0',
            'rate_vehicle' => 'nullable|numeric|min:0',
            'rate_group' => 'nullable|numeric|min:0',
        ]);
        $hotel->accommodationActivities()->create($data);
        return back()->with('success', 'Activity added.');
    }

    public function updateActivity(Request $request, Hotel $hotel, AccommodationActivityModel $activity)
    {
        $this->authorizeManage($hotel);
        if ((int) $activity->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_person' => 'required|numeric|min:0',
            'rate_adult' => 'nullable|numeric|min:0',
            'rate_child' => 'nullable|numeric|min:0',
            'rate_guide' => 'nullable|numeric|min:0',
            'rate_vehicle' => 'nullable|numeric|min:0',
            'rate_group' => 'nullable|numeric|min:0',
        ]);

        $activity->update($data);
        return back()->with('success', 'Activity updated.');
    }

    public function deleteActivity(Hotel $hotel, AccommodationActivityModel $activity)
    {
        $this->authorizeManage($hotel);
        $activity->delete();
        return back()->with('success', 'Activity deleted.');
    }

    // ─── Child Policies ────────────────────────────────────────

    public function storeChildPolicy(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'min_age' => 'required|integer|min:0',
            'max_age' => 'required|integer|min:0',
            'policy_type' => 'required|in:percentage,fixed,free',
            'value' => 'required|numeric|min:0',
            'sharing_type' => 'nullable|in:alone,with_adult',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_fixed' => 'nullable|numeric|min:0',
            'room_type_id' => 'nullable|exists:room_types,id',
            'meal_plan_id' => 'nullable|exists:meal_plans,id',
            'season_id' => 'nullable|exists:accommodation_seasons,id',
            'notes' => 'nullable|string',
        ]);
        $hotel->childPolicies()->create($data);
        return back()->with('success', 'Child policy added.');
    }

    public function updateChildPolicy(Request $request, Hotel $hotel, AccommodationChildPolicy $policy)
    {
        $this->authorizeManage($hotel);
        if ((int) $policy->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $data = $request->validate([
            'min_age' => 'required|integer|min:0',
            'max_age' => 'required|integer|min:0',
            'policy_type' => 'required|in:percentage,fixed,free',
            'value' => 'required|numeric|min:0',
            'sharing_type' => 'nullable|in:alone,with_adult',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_fixed' => 'nullable|numeric|min:0',
            'room_type_id' => 'nullable|exists:room_types,id',
            'meal_plan_id' => 'nullable|exists:meal_plans,id',
            'season_id' => 'nullable|exists:accommodation_seasons,id',
            'notes' => 'nullable|string',
        ]);

        $policy->update($data);
        return back()->with('success', 'Child policy updated.');
    }

    public function deleteChildPolicy(Hotel $hotel, AccommodationChildPolicy $policy)
    {
        $this->authorizeManage($hotel);
        $policy->delete();
        return back()->with('success', 'Child policy deleted.');
    }

    // ─── Payment Policies ──────────────────────────────────────

    public function storePaymentPolicy(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'days_before' => 'nullable|integer|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);
        $hotel->paymentPolicies()->create($data);
        return back()->with('success', 'Payment policy added.');
    }

    public function updatePaymentPolicy(Request $request, Hotel $hotel, AccommodationPaymentPolicy $policy)
    {
        $this->authorizeManage($hotel);
        if ((int) $policy->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'days_before' => 'nullable|integer|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $policy->update($data);
        return back()->with('success', 'Payment policy updated.');
    }

    public function deletePaymentPolicy(Hotel $hotel, AccommodationPaymentPolicy $policy)
    {
        $this->authorizeManage($hotel);
        $policy->delete();
        return back()->with('success', 'Payment policy deleted.');
    }

    // ─── Cancellation Policies ─────────────────────────────────

    public function storeCancellationPolicy(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'days_before' => 'required|integer|min:0',
            'penalty_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);
        $hotel->cancellationPolicies()->create($data);
        return back()->with('success', 'Cancellation policy added.');
    }

    public function updateCancellationPolicy(Request $request, Hotel $hotel, AccommodationCancellationPolicy $policy)
    {
        $this->authorizeManage($hotel);
        if ((int) $policy->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $data = $request->validate([
            'days_before' => 'required|integer|min:0',
            'penalty_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        $policy->update($data);
        return back()->with('success', 'Cancellation policy updated.');
    }

    public function deleteCancellationPolicy(Hotel $hotel, AccommodationCancellationPolicy $policy)
    {
        $this->authorizeManage($hotel);
        $policy->delete();
        return back()->with('success', 'Cancellation policy deleted.');
    }

    // ─── Tour Leader Discounts ─────────────────────────────────

    public function storeTourLeaderDiscount(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'min_pax' => 'required|integer|min:1',
            'max_pax' => 'nullable|integer|min:1',
            'discount_type' => 'required|in:free,percentage,fixed',
            'value' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
        $hotel->tourLeaderDiscounts()->create($data);
        return back()->with('success', 'Tour leader discount added.');
    }

    public function updateTourLeaderDiscount(Request $request, Hotel $hotel, AccommodationTourLeaderDiscount $discount)
    {
        $this->authorizeManage($hotel);
        if ((int) $discount->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $data = $request->validate([
            'min_pax' => 'required|integer|min:1',
            'max_pax' => 'nullable|integer|min:1',
            'discount_type' => 'required|in:free,percentage,fixed',
            'value' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $discount->update($data);
        return back()->with('success', 'Tour leader discount updated.');
    }

    public function storeBackupRate(Request $request, Hotel $hotel)
    {
        $this->authorizeManage($hotel);
        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'rate_year_id' => 'nullable|exists:accommodation_rate_years,id',
        ]);

        $rateYearId = $data['rate_year_id'] ?? $hotel->rateYears()->where('is_active', true)->value('id');
        if (!$rateYearId) {
            return back()->with('error', 'No active rate year to snapshot.');
        }

        $rates = AccommodationRoomRate::query()
            ->where('hotel_id', $hotel->id)
            ->where('rate_year_id', $rateYearId)
            ->orderBy('id')
            ->get()
            ->map(fn (AccommodationRoomRate $rate) => Arr::only($rate->toArray(), [
                'rate_year_id', 'season_id', 'room_category_id', 'room_type_id', 'meal_plan_id', 'rate_type_id',
                'rate_kind', 'sto_rate_raw', 'contracted_rate', 'promotional_rate', 'derived_rate', 'rate_source',
                'markup_percent', 'markup_fixed', 'adult_rate', 'child_rate', 'infant_rate',
                'single_supplement', 'per_person_sharing_double', 'per_person_sharing_twin', 'triple_adjustment',
                'currency', 'visibility_mode', 'is_override',
            ]))
            ->values()
            ->all();

        $version = (int) ($hotel->backupRates()->max('version_no') ?? 0) + 1;
        $hotel->backupRates()->create([
            'label' => $data['label'] ?? ('Snapshot v' . $version),
            'version_no' => $version,
            'snapshot_date' => now()->toDateString(),
            'source_rate_year_id' => $rateYearId,
            'rate_data' => [
                'rate_year_id' => $rateYearId,
                'rows' => $rates,
            ],
        ]);

        return back()->with('success', 'Backup snapshot saved.');
    }

    public function restoreBackupRate(Hotel $hotel, AccommodationBackupRate $backup)
    {
        $this->authorizeManage($hotel);
        if ((int) $backup->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $rows = (array) data_get($backup->rate_data, 'rows', []);
        foreach ($rows as $row) {
            $payload = array_merge($row, ['hotel_id' => $hotel->id]);
            $guard = $this->buildRateUniquenessGuard($payload);
            AccommodationRoomRate::updateOrCreate(
                ['hotel_id' => $hotel->id, 'rate_uniqueness_guard' => $guard],
                array_merge($payload, ['rate_uniqueness_guard' => $guard])
            );
        }

        return back()->with('success', 'Backup snapshot restored.');
    }

    public function deleteBackupRate(Hotel $hotel, AccommodationBackupRate $backup)
    {
        $this->authorizeManage($hotel);
        if ((int) $backup->hotel_id !== (int) $hotel->id) {
            abort(404);
        }

        $backup->delete();
        return back()->with('success', 'Backup snapshot deleted.');
    }

    public function deleteTourLeaderDiscount(Hotel $hotel, AccommodationTourLeaderDiscount $discount)
    {
        $this->authorizeManage($hotel);
        $discount->delete();
        return back()->with('success', 'Tour leader discount deleted.');
    }

    // ─── CSV Bulk Import ───────────────────────────────────────

    public function importCsv(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Only super admins can import accommodations.');
        }

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
