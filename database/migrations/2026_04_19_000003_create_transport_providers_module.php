<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transport Providers
        Schema::create('transport_providers', function (Blueprint $table) {
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

        // Vehicle Types
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // 4x4 5 Seater, Coaster, etc.
            $table->integer('capacity')->default(0);
            $table->string('category')->default('safari'); // safari, transfer, luxury
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Provider Vehicles (individual vehicles)
        Schema::create('provider_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('registration_number')->nullable();
            $table->string('make_model')->nullable();
            $table->integer('year_of_manufacture')->nullable();
            $table->string('color')->nullable();
            $table->string('status')->default('available'); // available, in_service, maintenance
            $table->timestamps();
        });

        // Drivers
        Schema::create('transport_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_expiry')->nullable();
            $table->json('languages')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // Transfer Routes
        Schema::create('transfer_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->string('origin');
            $table->string('destination');
            $table->integer('distance_km')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();
        });

        // Transport Gallery
        Schema::create('transport_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->boolean('is_cover')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Transport Rates
        Schema::create('transport_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transfer_route_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rate_type')->default('per_day'); // per_day, per_transfer, per_km
            $table->decimal('rate', 12, 2)->default(0);
            $table->string('season_name')->default('Year Round');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });

        // Fuel / Cost Settings
        Schema::create('transport_cost_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->decimal('fuel_cost_per_litre', 8, 2)->default(0);
            $table->decimal('driver_daily_rate', 12, 2)->default(0);
            $table->decimal('insurance_daily', 12, 2)->default(0);
            $table->decimal('maintenance_reserve', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_cost_settings');
        Schema::dropIfExists('transport_rates');
        Schema::dropIfExists('transport_media');
        Schema::dropIfExists('transfer_routes');
        Schema::dropIfExists('transport_drivers');
        Schema::dropIfExists('provider_vehicles');
        Schema::dropIfExists('vehicle_types');
        Schema::dropIfExists('transport_providers');
    }
};
