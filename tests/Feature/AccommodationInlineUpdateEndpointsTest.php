<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MasterData\AccommodationActivityModel;
use App\Models\MasterData\AccommodationBackupRate;
use App\Models\MasterData\AccommodationCancellationPolicy;
use App\Models\MasterData\AccommodationChildPolicy;
use App\Models\MasterData\AccommodationExtraFee;
use App\Models\MasterData\AccommodationHolidaySupplement;
use App\Models\MasterData\AccommodationPaymentPolicy;
use App\Models\MasterData\AccommodationRateYear;
use App\Models\MasterData\AccommodationRoomRate;
use App\Models\MasterData\AccommodationSeason;
use App\Models\MasterData\AccommodationTourLeaderDiscount;
use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\MealPlan;
use App\Models\MasterData\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationInlineUpdateEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_put_extra_fee_updates_inline_row(): void
    {
        $ctx = $this->createContext();

        $fee = AccommodationExtraFee::create([
            'hotel_id' => $ctx['hotel']->id,
            'name' => 'Old Fee',
            'fee_type' => 'flat',
            'amount' => 10,
        ]);

        $response = $this->actingAs($ctx['user'])->put('/accommodations/' . $ctx['hotel']->id . '/extra-fees/' . $fee->id, [
            'name' => 'Updated Fee',
            'fee_type' => 'per_person',
            'amount' => 45.5,
            'adult_rate' => 30,
            'child_rate' => 10,
            'resident_rate' => 20,
            'non_resident_rate' => 40,
            'apply_per' => 'person',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('accommodation_extra_fees', [
            'id' => $fee->id,
            'name' => 'Updated Fee',
            'fee_type' => 'per_person',
        ]);
    }

    public function test_put_holiday_supplement_updates_inline_row(): void
    {
        $ctx = $this->createContext();

        $supplement = AccommodationHolidaySupplement::create([
            'hotel_id' => $ctx['hotel']->id,
            'holiday_name' => 'Old Holiday',
            'start_date' => '2026-12-20',
            'end_date' => '2026-12-31',
            'supplement_amount' => 20,
            'apply_to' => 'per_person',
        ]);

        $response = $this->actingAs($ctx['user'])->put('/accommodations/' . $ctx['hotel']->id . '/holiday-supplements/' . $supplement->id, [
            'holiday_name' => 'Christmas',
            'start_date' => '2026-12-24',
            'end_date' => '2026-12-26',
            'supplement_amount' => 60,
            'apply_to' => 'per_room',
            'adult_rate' => 35,
            'child_rate' => 15,
            'room_type_id' => $ctx['roomType']->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('accommodation_holiday_supplements', [
            'id' => $supplement->id,
            'holiday_name' => 'Christmas',
            'apply_to' => 'per_room',
        ]);
    }

    public function test_put_activity_updates_inline_row(): void
    {
        $ctx = $this->createContext();

        $activity = AccommodationActivityModel::create([
            'hotel_id' => $ctx['hotel']->id,
            'name' => 'Old Activity',
            'price_per_person' => 25,
        ]);

        $response = $this->actingAs($ctx['user'])->put('/accommodations/' . $ctx['hotel']->id . '/activities/' . $activity->id, [
            'name' => 'Bush Walk',
            'description' => 'Guided walk',
            'price_per_person' => 75,
            'rate_adult' => 75,
            'rate_child' => 35,
            'rate_guide' => 20,
            'rate_vehicle' => 0,
            'rate_group' => 200,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('accommodation_activities', [
            'id' => $activity->id,
            'name' => 'Bush Walk',
            'price_per_person' => 75,
        ]);
    }

    public function test_put_child_policy_updates_inline_row(): void
    {
        $ctx = $this->createContext();

        $policy = AccommodationChildPolicy::create([
            'hotel_id' => $ctx['hotel']->id,
            'min_age' => 2,
            'max_age' => 11,
            'policy_type' => 'percentage',
            'value' => 50,
        ]);

        $response = $this->actingAs($ctx['user'])->put('/accommodations/' . $ctx['hotel']->id . '/child-policies/' . $policy->id, [
            'min_age' => 3,
            'max_age' => 12,
            'policy_type' => 'fixed',
            'value' => 40,
            'sharing_type' => 'with_adult',
            'discount_percentage' => 20,
            'discount_fixed' => 10,
            'room_type_id' => $ctx['roomType']->id,
            'meal_plan_id' => $ctx['mealPlan']->id,
            'season_id' => $ctx['season']->id,
            'notes' => 'Updated child policy',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('accommodation_child_policies', [
            'id' => $policy->id,
            'policy_type' => 'fixed',
            'sharing_type' => 'with_adult',
            'season_id' => $ctx['season']->id,
        ]);
    }

    public function test_put_payment_policy_updates_inline_row(): void
    {
        $ctx = $this->createContext();

        $policy = AccommodationPaymentPolicy::create([
            'hotel_id' => $ctx['hotel']->id,
            'title' => 'Old Payment',
            'content' => 'Old terms',
        ]);

        $response = $this->actingAs($ctx['user'])->put('/accommodations/' . $ctx['hotel']->id . '/payment-policies/' . $policy->id, [
            'title' => 'Deposit Terms',
            'content' => '30% on booking',
            'days_before' => 30,
            'percentage' => 30,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('accommodation_payment_policies', [
            'id' => $policy->id,
            'title' => 'Deposit Terms',
            'days_before' => 30,
        ]);
    }

    public function test_put_cancellation_policy_updates_inline_row(): void
    {
        $ctx = $this->createContext();

        $policy = AccommodationCancellationPolicy::create([
            'hotel_id' => $ctx['hotel']->id,
            'days_before' => 14,
            'penalty_percentage' => 25,
        ]);

        $response = $this->actingAs($ctx['user'])->put('/accommodations/' . $ctx['hotel']->id . '/cancellation-policies/' . $policy->id, [
            'days_before' => 10,
            'penalty_percentage' => 40,
            'description' => 'Updated cancellation terms',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('accommodation_cancellation_policies', [
            'id' => $policy->id,
            'days_before' => 10,
            'penalty_percentage' => 40,
        ]);
    }

    public function test_put_tour_leader_discount_updates_inline_row(): void
    {
        $ctx = $this->createContext();

        $discount = AccommodationTourLeaderDiscount::create([
            'hotel_id' => $ctx['hotel']->id,
            'min_pax' => 6,
            'discount_type' => 'free',
            'value' => 0,
        ]);

        $response = $this->actingAs($ctx['user'])->put('/accommodations/' . $ctx['hotel']->id . '/tour-leader-discounts/' . $discount->id, [
            'min_pax' => 8,
            'max_pax' => 12,
            'discount_type' => 'percentage',
            'value' => 100,
            'discount_percentage' => 100,
            'notes' => 'One free TL for larger groups',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('accommodation_tour_leader_discounts', [
            'id' => $discount->id,
            'min_pax' => 8,
            'discount_type' => 'percentage',
            'max_pax' => 12,
        ]);
    }

    public function test_backup_restore_reapplies_snapshot_rates(): void
    {
        $ctx = $this->createContext();

        $guard = $ctx['rateYear']->id . ':' . $ctx['season']->id . ':0:0:0';

        $rate = AccommodationRoomRate::create([
            'hotel_id' => $ctx['hotel']->id,
            'rate_year_id' => $ctx['rateYear']->id,
            'season_id' => $ctx['season']->id,
            'adult_rate' => 100,
            'derived_rate' => 120,
            'visibility_mode' => 'computed',
            'rate_uniqueness_guard' => $guard,
            'currency' => 'USD',
        ]);

        $backup = AccommodationBackupRate::create([
            'hotel_id' => $ctx['hotel']->id,
            'label' => 'Snapshot v1',
            'version_no' => 1,
            'snapshot_date' => '2026-04-23',
            'source_rate_year_id' => $ctx['rateYear']->id,
            'rate_data' => [
                'rate_year_id' => $ctx['rateYear']->id,
                'rows' => [
                    [
                        'rate_year_id' => $ctx['rateYear']->id,
                        'season_id' => $ctx['season']->id,
                        'room_category_id' => null,
                        'room_type_id' => null,
                        'meal_plan_id' => null,
                        'rate_type_id' => null,
                        'rate_kind' => 'sto',
                        'derived_rate' => 250,
                        'adult_rate' => 250,
                        'child_rate' => 80,
                        'infant_rate' => 0,
                        'single_supplement' => 40,
                        'per_person_sharing_double' => 250,
                        'per_person_sharing_twin' => 250,
                        'triple_adjustment' => 0,
                        'currency' => 'USD',
                        'visibility_mode' => 'computed',
                        'is_override' => false,
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($ctx['user'])->post('/accommodations/' . $ctx['hotel']->id . '/backup-rates/' . $backup->id . '/restore');

        $response->assertStatus(302);
        $rate->refresh();

        $this->assertEquals('250.00', (string) $rate->derived_rate);
        $this->assertEquals('250.00', (string) $rate->adult_rate);
    }

    private function createContext(): array
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company' . uniqid() . '@example.test',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Root',
            'email' => 'root' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $destination = Destination::create([
            'company_id' => $company->id,
            'name' => 'Serengeti',
            'country' => 'TZ',
        ]);

        $hotel = Hotel::create([
            'company_id' => $company->id,
            'name' => 'Test Lodge',
            'location_id' => $destination->id,
            'category' => 'midrange',
            'is_active' => true,
        ]);

        $rateYear = AccommodationRateYear::create([
            'hotel_id' => $hotel->id,
            'year' => 2026,
            'is_active' => true,
        ]);

        $season = AccommodationSeason::create([
            'rate_year_id' => $rateYear->id,
            'name' => 'Peak',
            'start_date' => '2026-06-01',
            'end_date' => '2026-09-30',
        ]);

        $roomType = RoomType::create([
            'hotel_id' => $hotel->id,
            'type' => 'single',
            'label' => 'Single',
            'max_adults' => 1,
        ]);

        $mealPlan = MealPlan::query()->where('name', 'BB')->firstOrFail();

        return [
            'company' => $company,
            'user' => $user,
            'destination' => $destination,
            'hotel' => $hotel,
            'rateYear' => $rateYear,
            'season' => $season,
            'roomType' => $roomType,
            'mealPlan' => $mealPlan,
        ];
    }
}
