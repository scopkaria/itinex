# Flight & Transport Module - Developer Guide

**Last Updated**: April 24, 2026  
**Status**: ✅ Core Implementation Complete

## 🚀 Quick Start

### 1. Run Database Migrations
```bash
php artisan migrate
```

### 2. Seed Test Data
```bash
php artisan db:seed
```

This will create:
- ✅ Flight provider with aircraft, routes, rates, and policies
- ✅ Transport provider with vehicles, drivers, routes, and rates
- ✅ Sample payment and cancellation policies
- ✅ Test data across all 3 seasons (Low, High, Peak)

### 3. Access the Dashboards
- **Flight Module**: `/pages/flight-dashboard` (view: flight-dashboard.blade.php)
- **Transport Module**: `/pages/transport-dashboard` (view: transport-dashboard.blade.php)

---

## 📚 API Endpoints Reference

### FLIGHT MODULE

#### Flight Providers
```
GET     /api/flight-providers              List all providers
POST    /api/flight-providers              Create provider
GET     /api/flight-providers/{id}         Get provider details
PUT     /api/flight-providers/{id}         Update provider
DELETE  /api/flight-providers/{id}         Delete provider
```

**Example - Create Flight Provider**:
```bash
curl -X POST http://localhost/api/flight-providers \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Premium Airways",
    "email": "info@premiumair.com",
    "phone": "+255 700 123 456",
    "markup": 12.50
  }'
```

#### Flight Pricing Engine

**Rate Years**:
```
GET     /api/flight-providers/{id}/pricing/years
POST    /api/flight-providers/{id}/pricing/years
PUT     /api/flight-providers/{id}/pricing/years/{year_id}
DELETE  /api/flight-providers/{id}/pricing/years/{year_id}
```

**Seasons** (nested under years):
```
GET     /api/flight-providers/{id}/pricing/years/{year_id}/seasons
POST    /api/flight-providers/{id}/pricing/years/{year_id}/seasons
PUT     /api/flight-providers/{id}/pricing/seasons/{season_id}
DELETE  /api/flight-providers/{id}/pricing/seasons/{season_id}
```

**Rate Types**:
```
GET     /api/flight-providers/{id}/pricing/rate-types
POST    /api/flight-providers/{id}/pricing/rate-types
PUT     /api/flight-providers/{id}/pricing/rate-types/{type_id}
DELETE  /api/flight-providers/{id}/pricing/rate-types/{type_id}
```

**Payment Policies**:
```
GET     /api/flight-providers/{id}/pricing/payment-policies
POST    /api/flight-providers/{id}/pricing/payment-policies
PUT     /api/flight-providers/{id}/pricing/payment-policies/{policy_id}
DELETE  /api/flight-providers/{id}/pricing/payment-policies/{policy_id}
```

**Cancellation Policies**:
```
GET     /api/flight-providers/{id}/pricing/cancellation-policies
POST    /api/flight-providers/{id}/pricing/cancellation-policies
PUT     /api/flight-providers/{id}/pricing/cancellation-policies/{policy_id}
DELETE  /api/flight-providers/{id}/pricing/cancellation-policies/{policy_id}
```

#### Aircraft Management
```
GET     /api/flight-providers/{id}/aircraft               List aircraft
POST    /api/flight-providers/{id}/aircraft               Add aircraft
PUT     /api/flight-providers/{id}/aircraft/{aircraft_id}  Update aircraft
DELETE  /api/flight-providers/{id}/aircraft/{aircraft_id}  Delete aircraft
```

---

### TRANSPORT MODULE

#### Transport Providers
```
GET     /api/transport-providers              List all providers
POST    /api/transport-providers              Create provider
GET     /api/transport-providers/{id}         Get provider details
PUT     /api/transport-providers/{id}         Update provider
DELETE  /api/transport-providers/{id}         Delete provider
```

#### Transport Pricing Engine

**Rate Years**:
```
GET     /api/transport-providers/{id}/pricing/years
POST    /api/transport-providers/{id}/pricing/years
```

**Seasons**:
```
GET     /api/transport-providers/{id}/pricing/years/{year_id}/seasons
POST    /api/transport-providers/{id}/pricing/years/{year_id}/seasons
```

**Rate Types**:
```
GET     /api/transport-providers/{id}/pricing/rate-types
POST    /api/transport-providers/{id}/pricing/rate-types
```

**Transfer Rates** (Core pricing matrix):
```
GET     /api/transport-providers/{id}/pricing/transfer-rates
POST    /api/transport-providers/{id}/pricing/transfer-rates
PUT     /api/transport-providers/{id}/pricing/transfer-rates/{rate_id}
DELETE  /api/transport-providers/{id}/pricing/transfer-rates/{rate_id}
```

**Empty Run Rates**:
```
GET     /api/transport-providers/{id}/pricing/empty-run-rates
POST    /api/transport-providers/{id}/pricing/empty-run-rates
```

**Payment & Cancellation Policies**:
```
GET     /api/transport-providers/{id}/pricing/payment-policies
POST    /api/transport-providers/{id}/pricing/payment-policies
GET     /api/transport-providers/{id}/pricing/cancellation-policies
POST    /api/transport-providers/{id}/pricing/cancellation-policies
```

#### Vehicle Management

**Vehicles**:
```
GET     /api/transport-providers/{id}/vehicles             List vehicles
POST    /api/transport-providers/{id}/vehicles             Add vehicle
PUT     /api/transport-providers/{id}/vehicles/{vehicle_id} Update vehicle
DELETE  /api/transport-providers/{id}/vehicles/{vehicle_id} Delete vehicle
```

**Drivers**:
```
GET     /api/transport-providers/{id}/vehicles/drivers
POST    /api/transport-providers/{id}/vehicles/drivers
PUT     /api/transport-providers/{id}/vehicles/drivers/{driver_id}
DELETE  /api/transport-providers/{id}/vehicles/drivers/{driver_id}
```

**Transfer Routes**:
```
GET     /api/transport-providers/{id}/vehicles/routes
POST    /api/transport-providers/{id}/vehicles/routes
PUT     /api/transport-providers/{id}/vehicles/routes/{route_id}
DELETE  /api/transport-providers/{id}/vehicles/routes/{route_id}
```

---

## 📋 Example API Calls

### Create a Flight Rate Year
```bash
curl -X POST http://localhost/api/flight-providers/1/pricing/years \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "year": 2027,
    "valid_from": "2027-01-01",
    "valid_to": "2027-12-31",
    "status": "draft"
  }'
```

### Create a Season
```bash
curl -X POST http://localhost/api/flight-providers/1/pricing/years/1/seasons \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "High",
    "start_date": "2027-06-01",
    "end_date": "2027-10-31"
  }'
```

### Create a Transfer Rate (Transport)
```bash
curl -X POST http://localhost/api/transport-providers/1/pricing/transfer-rates \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "transfer_route_id": 1,
    "vehicle_type_id": 2,
    "buy_price": 100.00,
    "sell_price": 200.00,
    "transport_season_id": 5
  }'
```

### Add a Vehicle
```bash
curl -X POST http://localhost/api/transport-providers/1/vehicles \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "registration_number": "TZA-100",
    "vehicle_type_id": 1,
    "make_model": "Toyota Land Cruiser",
    "fuel_type": "Diesel",
    "fuel_consumption_kmpl": 10.5,
    "scope": "safari"
  }'
```

### Add a Driver
```bash
curl -X POST http://localhost/api/transport-providers/1/vehicles/drivers \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Kusma",
    "phone": "+255 700 123 456",
    "date_of_birth": "1985-05-20",
    "license_number": "LIC-001",
    "license_type": "Class B",
    "license_expiry": "2028-12-31",
    "skill_level": "expert",
    "languages": ["English", "Swahili"]
  }'
```

---

## 🗂️ File Structure

```
app/
├── Models/MasterData/
│   ├── Flight*.php                    (Flight models)
│   ├── Transport*.php                 (Transport models)
│   └── FlightRateVersion.php          (Audit trail)
│
├── Http/Controllers/MasterData/
│   ├── FlightProviderController.php
│   ├── FlightAircraftController.php
│   ├── FlightPricingEngineController.php
│   ├── TransportProviderController.php
│   ├── TransportPricingEngineController.php
│   └── TransportVehicleController.php
│
database/
├── migrations/
│   ├── 2026_04_24_000001_enhance_flight_module_pricing_engine.php
│   └── 2026_04_24_000002_enhance_transport_module_pricing_engine.php
│
├── seeders/
│   ├── FlightProviderSeeder.php
│   ├── TransportProviderSeeder.php
│   └── DatabaseSeeder.php
│
resources/views/pages/
├── flight-dashboard.blade.php
└── transport-dashboard.blade.php
```

---

## 🔐 Security & Authorization

All controllers use **TenantScoped** trait to ensure:
- ✅ Company data isolation
- ✅ No cross-tenant data leakage
- ✅ Role-based access control

**Middleware Chain**:
1. `auth:sanctum` - Requires authentication
2. Controllers validate `company_id` matches user's company
3. Methods return 403 Forbidden for unauthorized access

---

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Test a Specific Controller
```bash
php artisan test --filter=FlightProviderControllerTest
```

### Database Testing
Tests use in-memory SQLite `:memory:` database for speed.

---

## 📊 Database Schema Reference

### Flight Module Tables
- `flight_providers` - Main provider record
- `aircraft_types` - Aircraft inventory
- `flight_routes` - Route definitions (origin → arrival)
- `flight_rate_years` - Fiscal year configurations
- `flight_seasons` - Seasonal pricing periods
- `flight_rate_types` - Markup/rate configurations
- `flight_seasonal_rates` - Base pricing per route/season
- `flight_child_pricing` - Age-based pricing rules
- `flight_payment_policies` - Payment schedule (days before → %)
- `flight_cancellation_policies` - Cancellation penalties
- `flight_rate_versions` - Audit trail for rate changes

### Transport Module Tables
- `transport_providers` - Main provider record
- `vehicle_types` - Vehicle categories (4x4, Coaster, etc.)
- `provider_vehicles` - Individual vehicles
- `transport_drivers` - Driver records with licenses
- `transfer_routes` - Route definitions (From → To)
- `transport_rate_years` - Fiscal year configurations
- `transport_seasons` - Seasonal pricing periods
- `transport_rate_types` - Markup rules
- `transport_transfer_rates` - **Core Matrix**: Route × Vehicle × Season
- `transport_empty_run_rates` - Empty vehicle pricing
- `transport_payment_policies` - Payment terms
- `transport_cancellation_policies` - Cancellation rules
- `transport_imprest_components` - Cost breakdown items
- `transport_vehicle_descriptions` - Vehicle images & text
- `transport_rate_versions` - Audit trail

---

## 💡 Key Features

✅ **Modular Design**
- Separate controllers for providers, pricing, vehicles
- Clean responsibility separation

✅ **Multi-Level Pricing**
- Years → Seasons → Rate Types → Base Rates
- Supports complex pricing scenarios

✅ **Rate Versioning**
- Complete audit trail of price changes
- Track who changed what and when

✅ **Flexible Rate Structure** (Transport)
- Buy Price (cost to provider)
- Sell Price (revenue from customer)
- Automatic margin calculation
- Per-transfer, per-day, per-km options

✅ **Comprehensive Policies**
- Payment scheduling
- Seasonal cancellation penalties
- Child pricing rules
- Charter vs. scheduled flights

✅ **Test Data Ready**
- Seeders populate realistic sample data
- 3 seasons (Low, High, Peak)
- Multiple routes and rate types
- Production-like structure

---

## 🔧 Customization

### Adding New Rate Type
```php
$rateType = FlightRateType::create([
    'flight_provider_id' => $provider->id,
    'name' => 'VIP',
    'markup_percentage' => 25.00,
    'markup_fixed' => 0,
]);
```

### Creating Custom Route
```php
$route = FlightRoute::create([
    'flight_provider_id' => $provider->id,
    'origin_destination_id' => $nairobi->id,
    'arrival_destination_id' => $dar->id,
    'flight_duration_minutes' => 180,
]);
```

### Setting Seasonal Rates
```php
$rate = FlightSeasonalRate::create([
    'flight_provider_id' => $provider->id,
    'flight_route_id' => $route->id,
    'season_name' => 'High',
    'adult_rate' => 500.00,
    'child_rate' => 250.00,
]);
```

---

## 🎯 Next Steps

1. **UI Views** - Build pricing engine sidebar forms
2. **Integration** - Connect to itinerary builder
3. **Analytics** - Add rate history visualization
4. **Caching** - Optimize frequently-used rate queries
5. **Bulk Operations** - Import rates from CSV

---

## 📞 Support

**Issues?** Check:
- Migration status: `php artisan migrate:status`
- Seeder output: `php artisan db:seed --class=FlightProviderSeeder`
- Laravel logs: `storage/logs/laravel.log`

**Debug query issues**:
```php
DB::enableQueryLog();
// Your code...
dd(DB::getQueryLog());
```
