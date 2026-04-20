<?php

use App\Http\Controllers\Pdf\PdfController;
use App\Http\Controllers\Web\AccommodationController;
use App\Http\Controllers\Web\FlightProviderController;
use App\Http\Controllers\Web\MiscellaneousController;
use App\Http\Controllers\Web\TransportProviderController;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebAuthController::class, 'showLogin']);
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);

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
    Route::get('/accommodations/{hotel}/edit', [AccommodationController::class, 'edit']);
    Route::put('/accommodations/{hotel}', [AccommodationController::class, 'update']);
    Route::delete('/accommodations/{hotel}', [AccommodationController::class, 'delete']);
    Route::post('/accommodations/{hotel}/room-categories', [AccommodationController::class, 'storeRoomCategory']);
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
    Route::delete('/accommodations/{hotel}/extra-fees/{fee}', [AccommodationController::class, 'deleteExtraFee']);
    Route::post('/accommodations/{hotel}/holiday-supplements', [AccommodationController::class, 'storeHolidaySupplement']);
    Route::delete('/accommodations/{hotel}/holiday-supplements/{supplement}', [AccommodationController::class, 'deleteHolidaySupplement']);
    Route::post('/accommodations/{hotel}/activities', [AccommodationController::class, 'storeActivity']);
    Route::delete('/accommodations/{hotel}/activities/{activity}', [AccommodationController::class, 'deleteActivity']);
    Route::post('/accommodations/{hotel}/child-policies', [AccommodationController::class, 'storeChildPolicy']);
    Route::delete('/accommodations/{hotel}/child-policies/{policy}', [AccommodationController::class, 'deleteChildPolicy']);
    Route::post('/accommodations/{hotel}/payment-policies', [AccommodationController::class, 'storePaymentPolicy']);
    Route::delete('/accommodations/{hotel}/payment-policies/{policy}', [AccommodationController::class, 'deletePaymentPolicy']);
    Route::post('/accommodations/{hotel}/cancellation-policies', [AccommodationController::class, 'storeCancellationPolicy']);
    Route::delete('/accommodations/{hotel}/cancellation-policies/{policy}', [AccommodationController::class, 'deleteCancellationPolicy']);
    Route::post('/accommodations/{hotel}/tour-leader-discounts', [AccommodationController::class, 'storeTourLeaderDiscount']);
    Route::delete('/accommodations/{hotel}/tour-leader-discounts/{discount}', [AccommodationController::class, 'deleteTourLeaderDiscount']);
    Route::post('/accommodations/import-csv', [AccommodationController::class, 'importCsv']);

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

    // ── Miscellaneous (Add-on Costs) ──
    Route::get('/miscellaneous', [MiscellaneousController::class, 'index']);
    Route::post('/miscellaneous', [MiscellaneousController::class, 'store']);
    Route::put('/miscellaneous/{extra}', [MiscellaneousController::class, 'update']);
    Route::patch('/miscellaneous/{extra}/toggle', [MiscellaneousController::class, 'toggleActive']);
    Route::delete('/miscellaneous/{extra}', [MiscellaneousController::class, 'delete']);

    Route::get('/vehicles', [WebController::class, 'vehicles']);
    Route::post('/vehicles', [WebController::class, 'storeVehicle']);
    Route::delete('/vehicles/{vehicle}', [WebController::class, 'deleteVehicle']);

    // (Park fees merged into destinations)

    Route::get('/activities', [WebController::class, 'activities']);
    Route::post('/activities', [WebController::class, 'storeActivity']);
    Route::delete('/activities/{activity}', [WebController::class, 'deleteActivity']);

    Route::get('/extras', [WebController::class, 'extras']);
    Route::post('/extras', [WebController::class, 'storeExtra']);
    Route::delete('/extras/{extra}', [WebController::class, 'deleteExtra']);

    Route::get('/flights', [WebController::class, 'flights']);
    Route::post('/flights', [WebController::class, 'storeFlight']);
    Route::delete('/flights/{flight}', [WebController::class, 'deleteFlight']);

    // ── Itineraries ──
    Route::get('/itineraries', [WebController::class, 'itineraries']);
    Route::post('/itineraries', [WebController::class, 'storeItinerary']);
    Route::get('/itineraries/{itinerary}', [WebController::class, 'showItinerary']);
    Route::delete('/itineraries/{itinerary}', [WebController::class, 'deleteItinerary']);
    Route::post('/itineraries/{itinerary}/items', [WebController::class, 'storeItem']);
    Route::delete('/itineraries/{itinerary}/items/{item}', [WebController::class, 'deleteItem']);
    Route::post('/itineraries/{itinerary}/markup', [WebController::class, 'applyMarkup']);

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
