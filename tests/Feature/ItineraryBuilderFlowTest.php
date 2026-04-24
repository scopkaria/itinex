<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Itinerary\Itinerary;
use App\Models\Itinerary\ItineraryDay;
use App\Models\MasterData\Destination;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\FlightRateType;
use App\Models\MasterData\FlightRoute;
use App\Models\MasterData\FlightSeason;
use App\Models\MasterData\Package;
use App\Models\MasterData\ScheduledFlight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItineraryBuilderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_state_save_persists_json_payload(): void
    {
        $ctx = $this->makeContext();

        $payload = [
            'builder_state' => [
                'general' => [
                    'booking_name' => 'AQ Test Booking',
                    'booking_type' => 'agent',
                ],
                'save' => [
                    'status' => 'provisional',
                    'currency' => 'USD',
                ],
            ],
        ];

        $response = $this->actingAs($ctx['user'])
            ->postJson('/itineraries/' . $ctx['itinerary']->id . '/builder/state', $payload);

        $response->assertOk()
            ->assertJsonPath('builder_state.general.booking_name', 'AQ Test Booking')
            ->assertJsonPath('builder_state.save.status', 'provisional');

        $this->assertDatabaseHas('itineraries', [
            'id' => $ctx['itinerary']->id,
        ]);

        $ctx['itinerary']->refresh();
        $this->assertSame('AQ Test Booking', data_get($ctx['itinerary']->builder_state, 'general.booking_name'));
    }

    public function test_quote_service_flight_sto_returns_422_when_no_rate_match(): void
    {
        $ctx = $this->makeContext();

        $provider = FlightProvider::create([
            'company_id' => $ctx['company']->id,
            'name' => 'No Match Air',
            'email' => 'nomatch@example.test',
            'vat_type' => 'inclusive',
            'markup' => 0,
            'is_active' => true,
        ]);

        $route = FlightRoute::create([
            'flight_provider_id' => $provider->id,
            'origin_destination_id' => $ctx['origin']->id,
            'arrival_destination_id' => $ctx['arrival']->id,
        ]);

        $response = $this->actingAs($ctx['user'])->postJson(
            '/itineraries/' . $ctx['itinerary']->id . '/builder/quote-service',
            [
                'service_type' => 'flight',
                'payload' => [
                    'provider_id' => $provider->id,
                    'route_id' => $route->id,
                    'date' => '2026-07-15',
                    'rate_type' => 'STO',
                    'adults' => 2,
                    'teens' => 0,
                    'children' => 0,
                ],
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('message', 'ERROR: No rate found');
    }

    public function test_quote_service_flight_sto_returns_priced_item_when_rate_matches(): void
    {
        $ctx = $this->makeContext();

        $provider = FlightProvider::create([
            'company_id' => $ctx['company']->id,
            'name' => 'Match Air',
            'email' => 'match@example.test',
            'vat_type' => 'inclusive',
            'markup' => 0,
            'is_active' => true,
        ]);

        $route = FlightRoute::create([
            'flight_provider_id' => $provider->id,
            'origin_destination_id' => $ctx['origin']->id,
            'arrival_destination_id' => $ctx['arrival']->id,
        ]);

        $season = FlightSeason::create([
            'flight_provider_id' => $provider->id,
            'name' => 'High',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'display_order' => 1,
        ]);

        $rateType = FlightRateType::create([
            'flight_provider_id' => $provider->id,
            'name' => 'STO',
            'markup_percentage' => 0,
            'markup_fixed' => 0,
        ]);

        $scheduled = ScheduledFlight::create([
            'flight_provider_id' => $provider->id,
            'flight_route_id' => $route->id,
            'flight_season_id' => $season->id,
            'flight_rate_type_id' => $rateType->id,
            'flight_number' => 'AQ100',
            'base_adult_price' => 250,
            'base_child_price' => 120,
            'is_active' => true,
        ]);

        $response = $this->actingAs($ctx['user'])->postJson(
            '/itineraries/' . $ctx['itinerary']->id . '/builder/quote-service',
            [
                'service_type' => 'flight',
                'payload' => [
                    'provider_id' => $provider->id,
                    'route_id' => $route->id,
                    'date' => '2026-07-15',
                    'rate_type' => 'STO',
                    'adults' => 2,
                    'teens' => 1,
                    'children' => 1,
                    'child_ages' => [8],
                ],
            ]
        );

        $response->assertOk()
            ->assertJsonPath('item.reference_source', 'scheduled_flight')
            ->assertJsonPath('item.reference_id', $scheduled->id);

        $this->assertGreaterThan(0, (float) $response->json('base_total'));
    }

    public function test_adding_quoted_package_item_recalculates_itinerary_totals(): void
    {
        $ctx = $this->makeContext();

        $package = Package::create([
            'company_id' => $ctx['company']->id,
            'destination_id' => $ctx['arrival']->id,
            'name' => 'Northern Loop 3N',
            'code' => 'PKG-001',
            'nights' => 3,
            'price_mode' => 'per_person',
            'base_price' => 100,
            'markup_percentage' => 10,
            'discount_mode' => 'fixed',
            'discount_value' => 20,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $quoteResponse = $this->actingAs($ctx['user'])->postJson(
            '/itineraries/' . $ctx['itinerary']->id . '/builder/quote-service',
            [
                'service_type' => 'package',
                'payload' => [
                    'package_id' => $package->id,
                    'pax_total' => 2,
                ],
            ]
        );

        $quoteResponse->assertOk()
            ->assertJsonPath('item.reference_source', 'package')
            ->assertJsonPath('item.reference_id', $package->id);

        $item = $quoteResponse->json('item');
        $this->assertNotNull($item);

        $addResponse = $this->actingAs($ctx['user'])->postJson('/itineraries/' . $ctx['itinerary']->id . '/items', [
            'itinerary_day_id' => $ctx['day']->id,
            'type' => $item['type'],
            'reference_id' => $item['reference_id'],
            'reference_source' => $item['reference_source'],
            'quantity' => $item['quantity'],
            'meta' => $item['meta'],
        ]);

        $addResponse->assertStatus(302);

        $ctx['itinerary']->refresh();

        $this->assertSame('200.00', number_format((float) $ctx['itinerary']->total_cost, 2, '.', ''));
        $this->assertSame('200.00', number_format((float) $ctx['itinerary']->total_price, 2, '.', ''));
    }

    public function test_reschedule_endpoint_moves_itinerary_dates_and_day_rows(): void
    {
        $ctx = $this->makeContext();

        $response = $this->actingAs($ctx['user'])->postJson(
            '/itineraries/' . $ctx['itinerary']->id . '/builder/reschedule',
            ['start_date' => '2026-08-10']
        );

        $response->assertOk();

        $this->assertStringStartsWith('2026-08-10', (string) $response->json('itinerary.start_date'));
        $this->assertStringStartsWith('2026-08-12', (string) $response->json('itinerary.end_date'));

        $this->assertDatabaseHas('itinerary_days', [
            'id' => $ctx['day']->id,
            'day_number' => 1,
            'date' => '2026-08-10 00:00:00',
        ]);
    }

    private function makeContext(): array
    {
        $company = Company::create([
            'name' => 'AQ Co',
            'email' => 'aq' . uniqid() . '@example.test',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Builder Admin',
            'email' => 'builder' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $origin = Destination::create([
            'company_id' => $company->id,
            'name' => 'Arusha',
            'country' => 'TZ',
        ]);

        $arrival = Destination::create([
            'company_id' => $company->id,
            'name' => 'Seronera',
            'country' => 'TZ',
        ]);

        $itinerary = Itinerary::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'client_name' => 'AQ Client',
            'number_of_people' => 2,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-03',
            'total_days' => 3,
            'total_cost' => 0,
            'total_price' => 0,
            'profit' => 0,
            'markup_percentage' => 0,
            'margin_percentage' => 0,
        ]);

        $day = ItineraryDay::create([
            'itinerary_id' => $itinerary->id,
            'day_number' => 1,
            'date' => '2026-07-01',
        ]);

        return compact('company', 'user', 'origin', 'arrival', 'itinerary', 'day');
    }
}
