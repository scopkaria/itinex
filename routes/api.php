<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CostSheet\CostSheetController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Itinerary\ItineraryController;
use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\DestinationController;
use App\Http\Controllers\MasterData\ExtraController;
use App\Http\Controllers\MasterData\FlightController;
use App\Http\Controllers\MasterData\HotelController;
use App\Http\Controllers\MasterData\HotelRateController;
use App\Http\Controllers\MasterData\MealPlanController;
use App\Http\Controllers\MasterData\ParkFeeController;
use App\Http\Controllers\MasterData\RoomTypeController;
use App\Http\Controllers\MasterData\VehicleController;
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
    Route::apiResource('hotels.room-types', RoomTypeController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('hotels.rates', HotelRateController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('meal-plans', MealPlanController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('park-fees', ParkFeeController::class)->except(['show']);
    Route::apiResource('vehicles', VehicleController::class)->except(['show']);
    Route::apiResource('activities', ActivityController::class)->except(['show']);
    Route::apiResource('extras', ExtraController::class)->except(['show']);
    Route::apiResource('flights', FlightController::class)->except(['show']);

    // ── Itinerary Engine ───────────────────────────────────────
    Route::apiResource('itineraries', ItineraryController::class);
    Route::post('itineraries/{itinerary}/days', [ItineraryController::class, 'addDay']);
    Route::delete('itineraries/{itinerary}/days/{day}', [ItineraryController::class, 'removeDay']);
    Route::post('itineraries/{itinerary}/days/{day}/items', [ItineraryController::class, 'addItem']);
    Route::put('itineraries/{itinerary}/days/{day}/items/{item}', [ItineraryController::class, 'updateItem']);
    Route::delete('itineraries/{itinerary}/days/{day}/items/{item}', [ItineraryController::class, 'removeItem']);
    Route::get('itineraries/{itinerary}/totals', [ItineraryController::class, 'totals']);

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
