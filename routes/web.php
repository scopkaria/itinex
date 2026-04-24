<?php

use App\Http\Controllers\Pdf\PdfController;
use App\Http\Controllers\Web\AccommodationController;
use App\Http\Controllers\Web\FlightProviderController;
use App\Http\Controllers\Web\MiscellaneousController;
use App\Http\Controllers\Web\ModuleWorkspaceController;
use App\Http\Controllers\Web\TransportProviderController;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebAuthController::class, 'showLogin']);
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::get('/itineraries/{itinerary}/preview', [WebController::class, 'publicPreview'])
    ->name('itineraries.public-preview')
    ->middleware('signed');
Route::get('/itineraries/preview/{token}', [WebController::class, 'publicPreviewByToken'])
    ->name('itineraries.public-preview-token');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');

    // ── Master Data ──
    Route::get('/destinations', [WebController::class, 'destinations']);
    Route::get('/destinations/create', [WebController::class, 'createDestination']);
    Route::post('/destinations', [WebController::class, 'storeDestination']);
    Route::post('/destinations/clone', [WebController::class, 'cloneDestination']);
    Route::get('/destinations/{destination}/edit', [WebController::class, 'editDestination']);
    Route::put('/destinations/{destination}', [WebController::class, 'updateDestination']);
    Route::delete('/destinations/{destination}', [WebController::class, 'deleteDestination']);
    Route::post('/destinations/{destination}/fees', [WebController::class, 'storeDestinationFee']);
    Route::put('/destinations/{destination}/fees/{fee}', [WebController::class, 'updateDestinationFee']);
    Route::delete('/destinations/{destination}/fees/{fee}', [WebController::class, 'deleteDestinationFee']);
    Route::post('/destinations/{destination}/clone-rates', [WebController::class, 'cloneRatesToYear']);
    Route::post('/destinations/{destination}/media', [WebController::class, 'uploadDestinationMedia']);
    Route::delete('/destinations/{destination}/media/{media}', [WebController::class, 'deleteDestinationMedia']);
    Route::patch('/destinations/{destination}/media/{media}/cover', [WebController::class, 'setCoverDestinationMedia']);
    Route::post('/destinations/{destination}/media/reorder', [WebController::class, 'reorderDestinationMedia']);

    // ── Country / Region API (for dependent dropdowns) ──
    Route::get('/api/countries/{country}/regions', function (\App\Models\Country $country) {
        return response()->json($country->regions()->orderBy('name')->get(['id', 'name']));
    });
    Route::post('/api/countries/{country}/regions', function (\Illuminate\Http\Request $request, \App\Models\Country $country) {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $region = $country->regions()->firstOrCreate(['name' => $data['name']]);
        return response()->json($region);
    });

    Route::get('/hotels', [WebController::class, 'hotels']);
    Route::post('/hotels', [WebController::class, 'storeHotel']);
    Route::delete('/hotels/{hotel}', [WebController::class, 'deleteHotel']);

    // ── Accommodation (new module) ──
    Route::get('/accommodations', [AccommodationController::class, 'index']);
    Route::post('/accommodations', [AccommodationController::class, 'store']);
    Route::get('/accommodations/{hotel}', [AccommodationController::class, 'show']);
    Route::get('/accommodations/{hotel}/manage', [AccommodationController::class, 'manage']);
    Route::get('/accommodations/{hotel}/edit', [AccommodationController::class, 'edit']);
    Route::put('/accommodations/{hotel}', [AccommodationController::class, 'update']);
    Route::delete('/accommodations/{hotel}', [AccommodationController::class, 'delete']);
    Route::post('/accommodations/{hotel}/room-categories', [AccommodationController::class, 'storeRoomCategory']);
    Route::post('/accommodations/{hotel}/room-types/sync', [AccommodationController::class, 'syncRoomTypes']);
    Route::post('/accommodations/{hotel}/meal-plans', [AccommodationController::class, 'storeMealPlan']);
    Route::post('/accommodations/{hotel}/owners', [AccommodationController::class, 'syncOwners']);
    Route::delete('/accommodations/{hotel}/room-categories/{category}', [AccommodationController::class, 'deleteRoomCategory']);
    Route::post('/accommodations/{hotel}/media', [AccommodationController::class, 'uploadMedia']);
    Route::delete('/accommodations/{hotel}/media/{media}', [AccommodationController::class, 'deleteMedia']);
    Route::patch('/accommodations/{hotel}/media/{media}/cover', [AccommodationController::class, 'setCoverMedia']);
    Route::post('/accommodations/{hotel}/media/reorder', [AccommodationController::class, 'reorderMedia']);
    Route::post('/accommodations/{hotel}/rate-years', [AccommodationController::class, 'storeRateYear']);
    Route::patch('/accommodations/{hotel}/rate-years/{year}/activate', [AccommodationController::class, 'activateRateYear']);
    Route::post('/accommodations/{hotel}/rate-years/{year}/clone', [AccommodationController::class, 'cloneRateYear']);
    Route::post('/accommodations/{hotel}/rate-years/{year}/seasons', [AccommodationController::class, 'storeSeason']);
    Route::delete('/accommodations/{hotel}/seasons/{season}', [AccommodationController::class, 'deleteSeason']);
    Route::post('/accommodations/{hotel}/room-rates', [AccommodationController::class, 'storeRoomRate']);
    Route::delete('/accommodations/{hotel}/room-rates/{rate}', [AccommodationController::class, 'deleteRoomRate']);
    Route::post('/accommodations/{hotel}/rate-types', [AccommodationController::class, 'storeRateType']);
    Route::delete('/accommodations/{hotel}/rate-types/{type}', [AccommodationController::class, 'deleteRateType']);
    Route::post('/accommodations/{hotel}/extra-fees', [AccommodationController::class, 'storeExtraFee']);
    Route::put('/accommodations/{hotel}/extra-fees/{fee}', [AccommodationController::class, 'updateExtraFee']);
    Route::delete('/accommodations/{hotel}/extra-fees/{fee}', [AccommodationController::class, 'deleteExtraFee']);
    Route::post('/accommodations/{hotel}/holiday-supplements', [AccommodationController::class, 'storeHolidaySupplement']);
    Route::put('/accommodations/{hotel}/holiday-supplements/{supplement}', [AccommodationController::class, 'updateHolidaySupplement']);
    Route::delete('/accommodations/{hotel}/holiday-supplements/{supplement}', [AccommodationController::class, 'deleteHolidaySupplement']);
    Route::post('/accommodations/{hotel}/activities', [AccommodationController::class, 'storeActivity']);
    Route::put('/accommodations/{hotel}/activities/{activity}', [AccommodationController::class, 'updateActivity']);
    Route::delete('/accommodations/{hotel}/activities/{activity}', [AccommodationController::class, 'deleteActivity']);
    Route::post('/accommodations/{hotel}/child-policies', [AccommodationController::class, 'storeChildPolicy']);
    Route::put('/accommodations/{hotel}/child-policies/{policy}', [AccommodationController::class, 'updateChildPolicy']);
    Route::delete('/accommodations/{hotel}/child-policies/{policy}', [AccommodationController::class, 'deleteChildPolicy']);
    Route::post('/accommodations/{hotel}/payment-policies', [AccommodationController::class, 'storePaymentPolicy']);
    Route::put('/accommodations/{hotel}/payment-policies/{policy}', [AccommodationController::class, 'updatePaymentPolicy']);
    Route::delete('/accommodations/{hotel}/payment-policies/{policy}', [AccommodationController::class, 'deletePaymentPolicy']);
    Route::post('/accommodations/{hotel}/cancellation-policies', [AccommodationController::class, 'storeCancellationPolicy']);
    Route::put('/accommodations/{hotel}/cancellation-policies/{policy}', [AccommodationController::class, 'updateCancellationPolicy']);
    Route::delete('/accommodations/{hotel}/cancellation-policies/{policy}', [AccommodationController::class, 'deleteCancellationPolicy']);
    Route::post('/accommodations/{hotel}/tour-leader-discounts', [AccommodationController::class, 'storeTourLeaderDiscount']);
    Route::put('/accommodations/{hotel}/tour-leader-discounts/{discount}', [AccommodationController::class, 'updateTourLeaderDiscount']);
    Route::delete('/accommodations/{hotel}/tour-leader-discounts/{discount}', [AccommodationController::class, 'deleteTourLeaderDiscount']);
    Route::post('/accommodations/{hotel}/backup-rates', [AccommodationController::class, 'storeBackupRate']);
    Route::post('/accommodations/{hotel}/backup-rates/{backup}/restore', [AccommodationController::class, 'restoreBackupRate']);
    Route::delete('/accommodations/{hotel}/backup-rates/{backup}', [AccommodationController::class, 'deleteBackupRate']);
    Route::post('/accommodations/import-csv', [AccommodationController::class, 'importCsv']);
    Route::get('/accommodations/{hotel}/{section}', [ModuleWorkspaceController::class, 'accommodation'])
        ->whereIn('section', ['content', 'structure', 'pricing', 'policies', 'settings'])
        ->middleware('role:super_admin,admin,staff,hotel');

    // ── Flight Providers ──
    Route::get('/flight-providers', [FlightProviderController::class, 'index']);
    Route::post('/flight-providers', [FlightProviderController::class, 'store']);
    Route::get('/flight-providers/{provider}/edit', [FlightProviderController::class, 'edit']);
    Route::put('/flight-providers/{provider}', [FlightProviderController::class, 'update']);
    Route::delete('/flight-providers/{provider}', [FlightProviderController::class, 'delete']);
    Route::post('/flight-providers/{provider}/aircraft-types', [FlightProviderController::class, 'storeAircraftType']);
    Route::delete('/flight-providers/{provider}/aircraft-types/{type}', [FlightProviderController::class, 'deleteAircraftType']);
    Route::post('/flight-providers/{provider}/routes', [FlightProviderController::class, 'storeRoute']);
    Route::delete('/flight-providers/{provider}/routes/{route}', [FlightProviderController::class, 'deleteRoute']);
    Route::post('/flight-providers/{provider}/seasonal-rates', [FlightProviderController::class, 'storeSeasonalRate']);
    Route::delete('/flight-providers/{provider}/seasonal-rates/{rate}', [FlightProviderController::class, 'deleteSeasonalRate']);
    Route::post('/flight-providers/{provider}/scheduled-flights', [FlightProviderController::class, 'storeScheduledFlight']);
    Route::delete('/flight-providers/{provider}/scheduled-flights/{flight}', [FlightProviderController::class, 'deleteScheduledFlight']);
    Route::post('/flight-providers/{provider}/charter-flights', [FlightProviderController::class, 'storeCharterFlight']);
    Route::delete('/flight-providers/{provider}/charter-flights/{flight}', [FlightProviderController::class, 'deleteCharterFlight']);
    Route::post('/flight-providers/{provider}/child-pricing', [FlightProviderController::class, 'storeChildPricing']);
    Route::delete('/flight-providers/{provider}/child-pricing/{pricing}', [FlightProviderController::class, 'deleteChildPricing']);
    Route::post('/flight-providers/{provider}/policies', [FlightProviderController::class, 'storePolicy']);
    Route::delete('/flight-providers/{provider}/policies/{policy}', [FlightProviderController::class, 'deletePolicy']);
    Route::get('/flight-providers/{provider}/{section}', [ModuleWorkspaceController::class, 'flight'])
        ->whereIn('section', ['content', 'structure', 'pricing', 'policies', 'settings'])
        ->middleware('role:super_admin,admin,staff,hotel');

    // ── Transport Providers ──
    Route::get('/transport-providers', [TransportProviderController::class, 'index']);
    Route::post('/transport-providers', [TransportProviderController::class, 'store']);
    Route::get('/transport-providers/{provider}/edit', [TransportProviderController::class, 'edit']);
    Route::put('/transport-providers/{provider}', [TransportProviderController::class, 'update']);
    Route::delete('/transport-providers/{provider}', [TransportProviderController::class, 'delete']);
    Route::post('/transport-providers/{provider}/vehicle-types', [TransportProviderController::class, 'storeVehicleType']);
    Route::delete('/transport-providers/{provider}/vehicle-types/{type}', [TransportProviderController::class, 'deleteVehicleType']);
    Route::post('/transport-providers/{provider}/vehicles', [TransportProviderController::class, 'storeVehicle']);
    Route::delete('/transport-providers/{provider}/vehicles/{vehicle}', [TransportProviderController::class, 'deleteVehicle']);
    Route::post('/transport-providers/{provider}/drivers', [TransportProviderController::class, 'storeDriver']);
    Route::delete('/transport-providers/{provider}/drivers/{driver}', [TransportProviderController::class, 'deleteDriver']);
    Route::post('/transport-providers/{provider}/transfer-routes', [TransportProviderController::class, 'storeTransferRoute']);
    Route::delete('/transport-providers/{provider}/transfer-routes/{route}', [TransportProviderController::class, 'deleteTransferRoute']);
    Route::post('/transport-providers/{provider}/media', [TransportProviderController::class, 'uploadMedia']);
    Route::delete('/transport-providers/{provider}/media/{media}', [TransportProviderController::class, 'deleteMedia']);
    Route::post('/transport-providers/{provider}/rates', [TransportProviderController::class, 'storeRate']);
    Route::delete('/transport-providers/{provider}/rates/{rate}', [TransportProviderController::class, 'deleteRate']);
    Route::put('/transport-providers/{provider}/cost-settings', [TransportProviderController::class, 'updateCostSettings']);
    Route::post('/transport-providers/{provider}/documents', [TransportProviderController::class, 'storeDocument']);
    Route::delete('/transport-providers/{provider}/documents/{document}', [TransportProviderController::class, 'deleteDocument']);
    Route::get('/transport-providers/{provider}/{section}', [ModuleWorkspaceController::class, 'transport'])
        ->whereIn('section', ['content', 'structure', 'pricing', 'policies', 'settings'])
        ->middleware('role:super_admin,admin,staff,hotel');

    // ── Miscellaneous (Add-on Costs) ──
    Route::get('/miscellaneous', [MiscellaneousController::class, 'index']);
    Route::post('/miscellaneous', [MiscellaneousController::class, 'store']);
    Route::put('/miscellaneous/{extra}', [MiscellaneousController::class, 'update']);
    Route::patch('/miscellaneous/{extra}/toggle', [MiscellaneousController::class, 'toggleActive']);
    Route::delete('/miscellaneous/{extra}', [MiscellaneousController::class, 'delete']);

    Route::get('/vehicles', [WebController::class, 'vehicles']);
    Route::post('/vehicles', [WebController::class, 'storeVehicle']);
    Route::delete('/vehicles/{vehicle}', [WebController::class, 'deleteVehicle']);

    Route::get('/park-fees', [WebController::class, 'parkFees']);
    Route::post('/park-fees', [WebController::class, 'storeParkFee']);
    Route::put('/park-fees/{parkFee}', [WebController::class, 'updateParkFee']);
    Route::delete('/park-fees/{parkFee}', [WebController::class, 'deleteParkFee']);

    Route::get('/activities', [WebController::class, 'activities']);
    Route::post('/activities', [WebController::class, 'storeActivity']);
    Route::delete('/activities/{activity}', [WebController::class, 'deleteActivity']);

    Route::get('/extras', [WebController::class, 'extras']);
    Route::post('/extras', [WebController::class, 'storeExtra']);
    Route::delete('/extras/{extra}', [WebController::class, 'deleteExtra']);

    Route::get('/packages', [WebController::class, 'packages']);
    Route::post('/packages', [WebController::class, 'storePackage']);
    Route::post('/packages/bulk', [WebController::class, 'bulkPackages']);
    Route::post('/packages/import-csv', [WebController::class, 'importPackagesCsv']);
    Route::get('/packages/export-csv', [WebController::class, 'exportPackagesCsv']);
    Route::get('/packages/template-csv', [WebController::class, 'templatePackagesCsv']);
    Route::put('/packages/{package}', [WebController::class, 'updatePackage']);
    Route::delete('/packages/{package}', [WebController::class, 'deletePackage']);

    Route::get('/flights', [WebController::class, 'flights']);
    Route::post('/flights', [WebController::class, 'storeFlight']);
    Route::delete('/flights/{flight}', [WebController::class, 'deleteFlight']);

    // ── Itineraries ──
    Route::get('/itineraries', [WebController::class, 'itineraries']);
    Route::get('/operations/safari-calendar', [WebController::class, 'safariCalendar']);
    Route::post('/itineraries', [WebController::class, 'storeItinerary']);
    Route::get('/itineraries/{itinerary}', [WebController::class, 'showItinerary']);
    Route::get('/itineraries/{itinerary}/builder', [WebController::class, 'showItineraryBuilder']);
    Route::delete('/itineraries/{itinerary}', [WebController::class, 'deleteItinerary']);
    Route::post('/itineraries/{itinerary}/items', [WebController::class, 'storeItem']);
    Route::delete('/itineraries/{itinerary}/items/{item}', [WebController::class, 'deleteItem']);
    Route::post('/itineraries/{itinerary}/builder/state', [WebController::class, 'saveItineraryBuilderState']);
    Route::post('/itineraries/{itinerary}/builder/reschedule', [WebController::class, 'rescheduleItinerary']);
    Route::post('/itineraries/{itinerary}/builder/quote-service', [WebController::class, 'quoteItineraryService']);
    Route::post('/itineraries/{itinerary}/markup', [WebController::class, 'applyMarkup']);
    Route::post('/itineraries/{itinerary}/share-token', [WebController::class, 'regenerateShareToken']);
    Route::delete('/itineraries/{itinerary}/share-token', [WebController::class, 'revokeShareToken']);

    // ── PDF Downloads ──
    Route::get('/itineraries/{itinerary}/pdf/itinerary', [PdfController::class, 'itinerary'])->name('pdf.itinerary');
    Route::get('/itineraries/{itinerary}/pdf/quotation', [PdfController::class, 'quotation'])->name('pdf.quotation');
    Route::get('/itineraries/{itinerary}/pdf/cost-sheet', [PdfController::class, 'costSheet'])->name('pdf.costSheet');

    // ── System ──
    Route::get('/geography', [WebController::class, 'geography']);
    Route::post('/geography/countries', [WebController::class, 'storeCountry']);
    Route::put('/geography/countries/{country}', [WebController::class, 'updateCountry']);
    Route::patch('/geography/countries/{country}/toggle', [WebController::class, 'toggleCountry']);
    Route::post('/geography/regions', [WebController::class, 'storeRegion']);
    Route::put('/geography/regions/{region}', [WebController::class, 'updateRegion']);
    Route::patch('/geography/regions/{region}/toggle', [WebController::class, 'toggleRegion']);
    Route::put('/geography/companies/{company}/access', [WebController::class, 'updateCompanyAccess']);

    Route::get('/companies', [WebController::class, 'companies']);
    Route::post('/companies', [WebController::class, 'storeCompany']);
    Route::get('/companies/{company}', [WebController::class, 'showCompany']);
    Route::delete('/companies/{company}', [WebController::class, 'deleteCompany']);

    Route::get('/users', [WebController::class, 'users']);
    Route::post('/users', [WebController::class, 'storeUser']);
    Route::delete('/users/{user}', [WebController::class, 'deleteUser']);
});
