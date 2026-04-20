<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Flight Providers
        Schema::create('flight_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('description')->nullable();
            $table->string('vat_type', 20)->default('inclusive');
            $table->decimal('markup', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Aircraft Types
        Schema::create('aircraft_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('capacity')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Flight Routes
        Schema::create('flight_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('origin_destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->foreignId('arrival_destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->string('origin_name')->nullable();
            $table->string('arrival_name')->nullable();
            $table->integer('flight_duration_minutes')->nullable();
            $table->timestamps();
        });

        // Seasonal Rates
        Schema::create('flight_seasonal_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flight_route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('aircraft_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('season_name')->default('Year Round');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->decimal('adult_rate', 12, 2)->default(0);
            $table->decimal('child_rate', 12, 2)->default(0);
            $table->decimal('infant_rate', 12, 2)->default(0);
            $table->decimal('charter_rate', 12, 2)->default(0);
            $table->string('rate_type')->default('scheduled'); // scheduled, charter
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });

        // Scheduled Flights
        Schema::create('scheduled_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flight_route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('aircraft_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('flight_number')->nullable();
            $table->time('departure_time')->nullable();
            $table->time('arrival_time')->nullable();
            $table->string('frequency')->default('daily'); // daily, weekdays, specific_days
            $table->json('operating_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Charter Flights
        Schema::create('charter_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flight_route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('aircraft_type_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('min_pax')->default(1);
            $table->decimal('total_charter_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Flight Child Pricing
        Schema::create('flight_child_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
            $table->integer('min_age')->default(0);
            $table->integer('max_age')->default(11);
            $table->string('pricing_type')->default('percentage'); // percentage, fixed, free
            $table->decimal('value', 8, 2)->default(0);
            $table->timestamps();
        });

        // Flight Policies
        Schema::create('flight_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
            $table->string('policy_type'); // baggage, cancellation, rebooking, general
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_policies');
        Schema::dropIfExists('flight_child_pricing');
        Schema::dropIfExists('charter_flights');
        Schema::dropIfExists('scheduled_flights');
        Schema::dropIfExists('flight_seasonal_rates');
        Schema::dropIfExists('flight_routes');
        Schema::dropIfExists('aircraft_types');
        Schema::dropIfExists('flight_providers');
    }
};
