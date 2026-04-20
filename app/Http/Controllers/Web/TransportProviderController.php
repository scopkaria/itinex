<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MasterData\ProviderVehicle;
use App\Models\MasterData\TransferRoute;
use App\Models\MasterData\TransportCostSetting;
use App\Models\MasterData\TransportDocument;
use App\Models\MasterData\TransportDriver;
use App\Models\MasterData\TransportMedia;
use App\Models\MasterData\TransportProvider;
use App\Models\MasterData\TransportRate;
use App\Models\MasterData\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportProviderController extends Controller
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

    private function authorize(TransportProvider $provider): void
    {
        if (!$this->isSuperAdmin() && $provider->company_id !== $this->companyId()) {
            abort(403);
        }
    }

    // ─── List ──────────────────────────────────────────────────

    public function index()
    {
        $providers = $this->scopedQuery(TransportProvider::class)
            ->withCount(['vehicleTypes', 'vehicles', 'drivers'])
            ->orderBy('name')
            ->get();
        $companies = $this->companiesForForm();
        return view('pages.transport-providers', compact('providers', 'companies'));
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
        $provider = TransportProvider::create($data);
        return redirect('/transport-providers/' . $provider->id . '/edit')->with('success', 'Transport provider created.');
    }

    // ─── Edit ──────────────────────────────────────────────────

    public function edit(TransportProvider $provider)
    {
        $this->authorize($provider);
        $provider->load([
            'vehicleTypes', 'vehicles.vehicleType', 'vehicles.driver', 'drivers',
            'transferRoutes', 'media', 'rates.vehicleType', 'rates.transferRoute',
            'costSettings', 'documents',
        ]);
        $companies = $this->companiesForForm();
        return view('pages.transport-provider-form', [
            'provider' => $provider,
            'companies' => $companies,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, TransportProvider $provider)
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
        return back()->with('success', 'Transport provider updated.');
    }

    public function delete(TransportProvider $provider)
    {
        $this->authorize($provider);
        $provider->delete();
        return redirect('/transport-providers')->with('success', 'Transport provider deleted.');
    }

    // ─── Vehicle Types ─────────────────────────────────────────

    public function storeVehicleType(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'category' => 'required|in:safari,transfer,luxury',
            'description' => 'nullable|string',
        ]);
        $provider->vehicleTypes()->create($data);
        return back()->with('success', 'Vehicle type added.');
    }

    public function deleteVehicleType(TransportProvider $provider, VehicleType $type)
    {
        $this->authorize($provider);
        $type->delete();
        return back()->with('success', 'Vehicle type deleted.');
    }

    // ─── Provider Vehicles ─────────────────────────────────────

    public function storeVehicle(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'registration_number' => 'nullable|string|max:50',
            'make_model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year_of_manufacture' => 'nullable|integer|min:1990|max:2050',
            'color' => 'nullable|string|max:50',
            'branch' => 'nullable|string|max:255',
            'driver_id' => 'nullable|exists:transport_drivers,id',
            'fuel_type' => 'nullable|in:petrol,diesel,electric,hybrid',
            'engine_number' => 'nullable|string|max:100',
            'chassis_number' => 'nullable|string|max:100',
            'seats' => 'nullable|integer|min:1|max:100',
            'scope' => 'nullable|in:safari,transfer,both',
            'fuel_consumption' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,in_service,maintenance',
        ]);
        $provider->vehicles()->create($data);
        return back()->with('success', 'Vehicle added.');
    }

    public function deleteVehicle(TransportProvider $provider, ProviderVehicle $vehicle)
    {
        $this->authorize($provider);
        $vehicle->delete();
        return back()->with('success', 'Vehicle deleted.');
    }

    // ─── Drivers ───────────────────────────────────────────────

    public function storeDriver(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|string|max:50',
            'languages' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);
        $provider->drivers()->create($data);
        return back()->with('success', 'Driver added.');
    }

    public function deleteDriver(TransportProvider $provider, TransportDriver $driver)
    {
        $this->authorize($provider);
        $driver->delete();
        return back()->with('success', 'Driver deleted.');
    }

    // ─── Transfer Routes ───────────────────────────────────────

    public function storeTransferRoute(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'distance_km' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
        ]);
        $provider->transferRoutes()->create($data);
        return back()->with('success', 'Transfer route added.');
    }

    public function deleteTransferRoute(TransportProvider $provider, TransferRoute $route)
    {
        $this->authorize($provider);
        $route->delete();
        return back()->with('success', 'Transfer route deleted.');
    }

    // ─── Media / Gallery ───────────────────────────────────────

    public function uploadMedia(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $request->validate(['media' => 'required|image|max:5120']);
        $path = $request->file('media')->store('transport/' . $provider->id, 'public');
        $provider->media()->create([
            'file_path' => $path,
            'sort_order' => $provider->media()->count(),
        ]);
        return back()->with('success', 'Image uploaded.');
    }

    public function deleteMedia(TransportProvider $provider, TransportMedia $media)
    {
        $this->authorize($provider);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($media->file_path);
        $media->delete();
        return back()->with('success', 'Image deleted.');
    }

    // ─── Rates ─────────────────────────────────────────────────

    public function storeRate(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'transfer_route_id' => 'nullable|exists:transfer_routes,id',
            'rate_type' => 'required|in:per_day,per_transfer,per_km',
            'rate' => 'required|numeric|min:0',
            'season_name' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'currency' => 'nullable|string|size:3',
        ]);
        $provider->rates()->create($data);
        return back()->with('success', 'Rate added.');
    }

    public function deleteRate(TransportProvider $provider, TransportRate $rate)
    {
        $this->authorize($provider);
        $rate->delete();
        return back()->with('success', 'Rate deleted.');
    }

    // ─── Cost Settings ─────────────────────────────────────────

    public function updateCostSettings(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'fuel_cost_per_litre' => 'required|numeric|min:0',
            'driver_daily_rate' => 'required|numeric|min:0',
            'insurance_daily' => 'required|numeric|min:0',
            'maintenance_reserve' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $provider->costSettings()
            ? $provider->costSettings()->update($data)
            : $provider->costSettings()->create($data);
        return back()->with('success', 'Cost settings updated.');
    }

    // ─── Documents ─────────────────────────────────────────────

    public function storeDocument(Request $request, TransportProvider $provider)
    {
        $this->authorize($provider);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:license,insurance,permit,registration,general',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('transport-documents/' . $provider->id, 'public');

        $provider->documents()->create([
            'title' => $data['title'],
            'type' => $data['type'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'expiry_date' => $data['expiry_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function deleteDocument(TransportProvider $provider, TransportDocument $document)
    {
        $this->authorize($provider);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }
}
