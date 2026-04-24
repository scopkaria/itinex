# Tourism ERP Architecture Blueprint

## Goals
- Accurate dynamic pricing from a single calculation engine
- Strict company-level data separation
- Role-based and field-level security
- Modular structure for Accommodation, Flights, Transport, Packages, Activities
- Scalable multi-company operation

## Layered Design

### 1) Configuration Layer (Reusable)
Shared setup entities:
- Destinations
- Meal plans
- Room types
- Vehicle types
- Seasons
- Rate types (STO, Special, Contract)
- Child age rules
- Policies

Rules:
- Configuration records are reusable references across modules
- No final totals are stored in configuration tables

### 2) Data Layer (Entities)
Business entities:
- Accommodations
- Flight providers and routes
- Transport providers, fleet, routes
- Activities and experiences
- Itinerary packages

Rules:
- Store only raw rates and references
- Keep content/structure separate from pricing
- Use company isolation (`company_id`) and provider ownership checks

### 3) Calculation Engine (Core)
Centralized engine computes totals dynamically:
- STO base rate
- Seasonal adjustments
- Room/meal/vehicle/route logic
- Child policy discounts
- Supplements and reductions
- Module add-ons (transport, flights, activities)
- Markup (% and fixed)
- VAT inclusive/exclusive

Rules:
- Never hardcode or persist final computed totals in source rate tables
- Use deterministic formulas and return structured breakdowns

## Security Model

### Roles
- `super_admin`: global control
- `admin`: company-wide control
- `staff`: operational access
- `hotel`: provider-bound access

### Sensitive Field Controls
- STO raw rates are restricted
- Hotel users can manage their own rates but cannot see markup or derived selling fields
- Company users can consume computed rates
- Cross-company access is blocked by tenant checks

### Required Controls
- RBAC checks in controllers/services
- Field-level visibility service for sensitive attributes
- Audit/versioning for rate changes
- Provider ownership validation for linked IDs

## UI/UX Structure

Each module should have clear separation:
- Content
- Structure
- Pricing
- Policies

Recommended sidebar:
- Overview
- Content
- Structure
- Pricing
- Policies
- Settings

## Scaling and Operations
- Per-company subscription constraints (`max_users`)
- Module toggles per company
- API-first pricing calculator endpoints
- Independent module services with shared pricing core

## Implemented Foundation in This Repository
- `app/Services/Pricing/PricingEngineService.php`
- `app/Services/Pricing/PricingInput.php`
- `app/Services/Pricing/PricingBreakdown.php`
- `app/Services/Pricing/RateVisibilityService.php`
- `database/migrations/2026_04_23_180000_add_subscription_fields_to_companies_table.php`
- Company plan-limit enforcement in `app/Http/Controllers/System/UserController.php`
- Accommodation pricing response sanitization in `app/Http/Controllers/MasterData/AccommodationPricingController.php`
