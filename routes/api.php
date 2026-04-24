<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CostSheet\CostSheetController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Itinerary\ItineraryController;
use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\AccommodationPricingController;
use App\Http\Controllers\MasterData\DestinationController;
use App\Http\Controllers\MasterData\ExtraController;
use App\Http\Controllers\MasterData\FlightController;
use App\Http\Controllers\MasterData\FlightProviderController;
use App\Http\Controllers\MasterData\FlightPricingEngineController;
use App\Http\Controllers\MasterData\FlightAircraftController;
use App\Http\Controllers\MasterData\HotelController;
use App\Http\Controllers\MasterData\HotelRateController;
use App\Http\Controllers\MasterData\MealPlanController;
use App\Http\Controllers\MasterData\ParkFeeController;
use App\Http\Controllers\MasterData\RoomTypeController;
use App\Http\Controllers\MasterData\VehicleController;
use App\Http\Controllers\MasterData\TransportProviderController;
use App\Http\Controllers\MasterData\TransportPricingEngineController;
use App\Http\Controllers\MasterData\TransportVehicleController;
use App\Http\Controllers\Pricing\PricingPolicyController;
use App\Http\Controllers\Profit\ProfitController;
use App\Http\Controllers\System\CompanyController;
use App\Http\Controllers\System\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Auth (public) ──────────────────────────────────────────
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ───────────────────────────────────────────────────
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/me', [LoginController::class, 'me']);

    // ── Dashboard ──────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ── Master Data ────────────────────────────────────────────
    Route::apiResource('destinations', DestinationController::class);
    Route::apiResource('hotels', HotelController::class);
    Route::get('accommodations/{hotel}/calculator-bundle', [AccommodationPricingController::class, 'calculatorBundle']);
    Route::apiResource('hotels.room-types', RoomTypeController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('hotels.rates', HotelRateController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('meal-plans', MealPlanController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('park-fees', ParkFeeController::class)->except(['show']);
    Route::apiResource('vehicles', VehicleController::class)->except(['show']);
    Route::apiResource('activities', ActivityController::class)->except(['show']);
    Route::apiResource('extras', ExtraController::class)->except(['show']);
    Route::apiResource('flights', FlightController::class)->except(['show']);

    // ── Flight Module (New Redesigned) ──────────────────────────
    Route::apiResource('flight-providers', FlightProviderController::class);
    Route::prefix('flight-providers/{flightProvider}/pricing')->group(function () {
        // Rate Years
        Route::get('years', [FlightPricingEngineController::class, 'indexYears']);
        Route::post('years', [FlightPricingEngineController::class, 'storeYear']);
        Route::put('years/{flightRateYear}', [FlightPricingEngineController::class, 'updateYear']);
        Route::delete('years/{flightRateYear}', [FlightPricingEngineController::class, 'destroyYear']);

        // Seasons (nested under years)
        Route::get('years/{flightRateYear}/seasons', [FlightPricingEngineController::class, 'indexSeasons']);
        Route::post('years/{flightRateYear}/seasons', [FlightPricingEngineController::class, 'storeSeason']);
        Route::put('seasons/{flightSeason}', [FlightPricingEngineController::class, 'updateSeason']);
        Route::delete('seasons/{flightSeason}', [FlightPricingEngineController::class, 'destroySeason']);

        // Rate Types
        Route::get('rate-types', [FlightPricingEngineController::class, 'indexRateTypes']);
        Route::post('rate-types', [FlightPricingEngineController::class, 'storeRateType']);
        Route::put('rate-types/{flightRateType}', [FlightPricingEngineController::class, 'updateRateType']);
        Route::delete('rate-types/{flightRateType}', [FlightPricingEngineController::class, 'destroyRateType']);

        // Payment Policies
        Route::get('payment-policies', [FlightPricingEngineController::class, 'indexPaymentPolicies']);
        Route::post('payment-policies', [FlightPricingEngineController::class, 'storePaymentPolicy']);
        Route::put('payment-policies/{flightPaymentPolicy}', [FlightPricingEngineController::class, 'updatePaymentPolicy']);
        Route::delete('payment-policies/{flightPaymentPolicy}', [FlightPricingEngineController::class, 'destroyPaymentPolicy']);

        // Cancellation Policies
        Route::get('cancellation-policies', [FlightPricingEngineController::class, 'indexCancellationPolicies']);
        Route::post('cancellation-policies', [FlightPricingEngineController::class, 'storeCancellationPolicy']);
        Route::put('cancellation-policies/{flightCancellationPolicy}', [FlightPricingEngineController::class, 'updateCancellationPolicy']);
        Route::delete('cancellation-policies/{flightCancellationPolicy}', [FlightPricingEngineController::class, 'destroyCancellationPolicy']);
    });

    // Aircraft Management
    Route::prefix('flight-providers/{flightProvider}/aircraft')->group(function () {
        Route::get('', [FlightAircraftController::class, 'index']);
        Route::post('', [FlightAircraftController::class, 'store']);
        Route::put('{aircraftType}', [FlightAircraftController::class, 'update']);
        Route::delete('{aircraftType}', [FlightAircraftController::class, 'destroy']);
    });

    // ── Transport Module (New Redesigned) ────────────────────────
    Route::apiResource('transport-providers', TransportProviderController::class);
    Route::prefix('transport-providers/{transportProvider}/pricing')->group(function () {
        // Rate Years
        Route::get('years', [TransportPricingEngineController::class, 'indexYears']);
        Route::post('years', [TransportPricingEngineController::class, 'storeYear']);
        Route::put('years/{transportRateYear}', [TransportPricingEngineController::class, 'updateYear']);
        Route::delete('years/{transportRateYear}', [TransportPricingEngineController::class, 'destroyYear']);

        // Seasons (nested under years)
        Route::get('years/{transportRateYear}/seasons', [TransportPricingEngineController::class, 'indexSeasons']);
        Route::post('years/{transportRateYear}/seasons', [TransportPricingEngineController::class, 'storeSeason']);
        Route::put('seasons/{transportSeason}', [TransportPricingEngineController::class, 'updateSeason']);
        Route::delete('seasons/{transportSeason}', [TransportPricingEngineController::class, 'destroySeason']);

        // Rate Types
        Route::get('rate-types', [TransportPricingEngineController::class, 'indexRateTypes']);
        Route::post('rate-types', [TransportPricingEngineController::class, 'storeRateType']);
        Route::put('rate-types/{transportRateType}', [TransportPricingEngineController::class, 'updateRateType']);
        Route::delete('rate-types/{transportRateType}', [TransportPricingEngineController::class, 'destroyRateType']);

        // Transfer Rates
        Route::get('transfer-rates', [TransportPricingEngineController::class, 'indexTransferRates']);
        Route::post('transfer-rates', [TransportPricingEngineController::class, 'storeTransferRate']);
        Route::put('transfer-rates/{transportTransferRate}', [TransportPricingEngineController::class, 'updateTransferRate']);
        Route::delete('transfer-rates/{transportTransferRate}', [TransportPricingEngineController::class, 'destroyTransferRate']);

        // Payment Policies
        Route::get('payment-policies', [TransportPricingEngineController::class, 'indexPaymentPolicies']);
        Route::post('payment-policies', [TransportPricingEngineController::class, 'storePaymentPolicy']);
        Route::put('payment-policies/{transportPaymentPolicy}', [TransportPricingEngineController::class, 'updatePaymentPolicy']);
        Route::delete('payment-policies/{transportPaymentPolicy}', [TransportPricingEngineController::class, 'destroyPaymentPolicy']);

        // Cancellation Policies
        Route::get('cancellation-policies', [TransportPricingEngineController::class, 'indexCancellationPolicies']);
        Route::post('cancellation-policies', [TransportPricingEngineController::class, 'storeCancellationPolicy']);
        Route::put('cancellation-policies/{transportCancellationPolicy}', [TransportPricingEngineController::class, 'updateCancellationPolicy']);
        Route::delete('cancellation-policies/{transportCancellationPolicy}', [TransportPricingEngineController::class, 'destroyCancellationPolicy']);
    });

    // Vehicle Management (Drivers, Vehicles, Routes)
    Route::prefix('transport-providers/{transportProvider}/vehicles')->group(function () {
        // Vehicle Types
        Route::get('types', [TransportVehicleController::class, 'indexVehicleTypes']);

        // Vehicles
        Route::get('', [TransportVehicleController::class, 'indexVehicles']);
        Route::post('', [TransportVehicleController::class, 'storeVehicle']);
        Route::put('{providerVehicle}', [TransportVehicleController::class, 'updateVehicle']);
        Route::delete('{providerVehicle}', [TransportVehicleController::class, 'destroyVehicle']);

        // Drivers
        Route::get('drivers', [TransportVehicleController::class, 'indexDrivers']);
        Route::post('drivers', [TransportVehicleController::class, 'storeDriver']);
        Route::put('drivers/{transportDriver}', [TransportVehicleController::class, 'updateDriver']);
        Route::delete('drivers/{transportDriver}', [TransportVehicleController::class, 'destroyDriver']);

        // Transfer Routes
        Route::get('routes', [TransportVehicleController::class, 'indexRoutes']);
        Route::post('routes', [TransportVehicleController::class, 'storeRoute']);
        Route::put('routes/{transferRoute}', [TransportVehicleController::class, 'updateRoute']);
        Route::delete('routes/{transferRoute}', [TransportVehicleController::class, 'destroyRoute']);
    });

    // ── Itinerary Engine ───────────────────────────────────────
    Route::apiResource('itineraries', ItineraryController::class);
    Route::post('itineraries/{itinerary}/days', [ItineraryController::class, 'addDay']);
    Route::delete('itineraries/{itinerary}/days/{day}', [ItineraryController::class, 'removeDay']);
    Route::post('itineraries/{itinerary}/days/{day}/items', [ItineraryController::class, 'addItem']);
    Route::put('itineraries/{itinerary}/days/{day}/items/{item}', [ItineraryController::class, 'updateItem']);
    Route::delete('itineraries/{itinerary}/days/{day}/items/{item}', [ItineraryController::class, 'removeItem']);
    Route::get('itineraries/{itinerary}/totals', [ItineraryController::class, 'totals']);
    Route::post('itineraries/{itinerary}/partner-overrides', [ItineraryController::class, 'upsertPartnerOverride']);
    Route::delete('itineraries/{itinerary}/partner-overrides', [ItineraryController::class, 'deletePartnerOverride']);

    // ── Unified Pricing Policy Adapters ─────────────────────
    Route::get('pricing-policies/{module}/{providerId}', [PricingPolicyController::class, 'show'])
        ->whereIn('module', ['accommodation', 'flight', 'transport']);

    // ── Cost Sheet ─────────────────────────────────────────────
    Route::get('itineraries/{itinerary}/cost-sheet', [CostSheetController::class, 'generate']);
    Route::get('itineraries/{itinerary}/cost-sheet/summary', [CostSheetController::class, 'summary']);
    Route::get('itineraries/{itinerary}/cost-sheet/categories', [CostSheetController::class, 'categoryTotals']);

    // ── Profit Engine ──────────────────────────────────────────
    Route::get('itineraries/{itinerary}/profit', [ProfitController::class, 'calculate']);
    Route::post('itineraries/{itinerary}/markup', [ProfitController::class, 'applyMarkup']);
    Route::get('itineraries/{itinerary}/pnl', [ProfitController::class, 'profitAndLoss']);

    // ── System (admin/super_admin only) ────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('companies', CompanyController::class);
    });

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});
