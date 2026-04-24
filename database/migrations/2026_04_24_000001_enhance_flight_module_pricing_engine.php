<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Flight Rate Years (manage valid years for pricing)
        if (! Schema::hasTable('flight_rate_years')) {
            Schema::create('flight_rate_years', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
                $table->year('year');
                $table->date('valid_from');
                $table->date('valid_to');
                $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
                $table->timestamps();
                $table->unique(['flight_provider_id', 'year']);
            });
        }

        // Flight Seasons (High, Low, Peak seasons)
        if (! Schema::hasTable('flight_seasons')) {
            Schema::create('flight_seasons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('flight_rate_year_id')->nullable()->constrained('flight_rate_years')->nullOnDelete();
                $table->string('name'); // High, Low, Peak
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        // Flight Rate Types (STO, Special, Contract, etc.)
        if (! Schema::hasTable('flight_rate_types')) {
            Schema::create('flight_rate_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // STO, Special, Contract
                $table->decimal('markup_percentage', 8, 2)->default(0);
                $table->decimal('markup_fixed', 12, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Enhanced Scheduled Flights with pricing structure
        Schema::table('scheduled_flights', function (Blueprint $table) {
            if (! Schema::hasColumn('scheduled_flights', 'flight_rate_year_id')) {
                $table->foreignId('flight_rate_year_id')->nullable()->constrained('flight_rate_years')->nullOnDelete();
            }
            if (! Schema::hasColumn('scheduled_flights', 'flight_season_id')) {
                $table->foreignId('flight_season_id')->nullable()->constrained('flight_seasons')->nullOnDelete();
            }
            if (! Schema::hasColumn('scheduled_flights', 'flight_rate_type_id')) {
                $table->foreignId('flight_rate_type_id')->nullable()->constrained('flight_rate_types')->nullOnDelete();
            }
            if (! Schema::hasColumn('scheduled_flights', 'base_adult_price')) {
                $table->decimal('base_adult_price', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('scheduled_flights', 'base_child_price')) {
                $table->decimal('base_child_price', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('scheduled_flights', 'base_guide_price')) {
                $table->decimal('base_guide_price', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('scheduled_flights', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // Charter Flight Rates (Multi-seasonal, aircraft-specific)
        if (! Schema::hasTable('flight_charter_rates')) {
            Schema::create('flight_charter_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('flight_route_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('aircraft_type_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('flight_season_id')->nullable()->constrained('flight_seasons')->nullOnDelete();
                $table->decimal('price_per_hour', 12, 2)->default(0);
                $table->decimal('min_price', 12, 2)->default(0);
                $table->integer('min_hours')->default(1);
                $table->timestamps();
            });
        }

        // Enhanced Flight Child Pricing with seasons
        if (Schema::hasTable('flight_child_pricing')) {
            Schema::table('flight_child_pricing', function (Blueprint $table) {
                if (! Schema::hasColumn('flight_child_pricing', 'flight_rate_year_id')) {
                    $table->foreignId('flight_rate_year_id')->nullable()->constrained('flight_rate_years')->nullOnDelete();
                }
                if (! Schema::hasColumn('flight_child_pricing', 'pricing_rule')) {
                    $table->enum('pricing_rule', ['free', 'child_rate', 'adult_rate'])->default('child_rate');
                }
                if (! Schema::hasColumn('flight_child_pricing', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });
        }

        // Flight Payment Policies (Days before arrival)
        if (! Schema::hasTable('flight_payment_policies')) {
            Schema::create('flight_payment_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
                $table->integer('days_before_arrival');
                $table->decimal('percentage_due', 5, 2);
                $table->timestamps();
            });
        }

        // Flight Cancellation Policies
        if (! Schema::hasTable('flight_cancellation_policies')) {
            Schema::create('flight_cancellation_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('flight_season_id')->nullable()->constrained('flight_seasons')->nullOnDelete();
                $table->integer('days_before_travel');
                $table->decimal('penalty_percentage', 5, 2);
                $table->timestamps();
            });
        }

        // Flight Rate History/Versioning (for audit trail)
        if (! Schema::hasTable('flight_rate_versions')) {
            Schema::create('flight_rate_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flight_provider_id')->constrained()->cascadeOnDelete();
                $table->string('entity_type'); // scheduled_flight, charter_rate, etc.
                $table->unsignedBigInteger('entity_id');
                $table->decimal('old_value', 12, 2)->nullable();
                $table->decimal('new_value', 12, 2)->nullable();
                $table->string('change_type'); // created, updated, deleted
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['entity_type', 'entity_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_rate_versions');
        Schema::dropIfExists('flight_cancellation_policies');
        Schema::dropIfExists('flight_payment_policies');
        Schema::dropIfExists('flight_charter_rates');
        
        // Drop new columns from existing tables
        if (Schema::hasTable('flight_child_pricing')) {
            Schema::table('flight_child_pricing', function (Blueprint $table) {
                $table->dropForeignIdFor('Flight_rate_year');
                $table->dropColumn(['flight_rate_year_id', 'pricing_rule', 'notes']);
            });
        }

        if (Schema::hasTable('scheduled_flights')) {
            Schema::table('scheduled_flights', function (Blueprint $table) {
                $table->dropForeignIdFor('flight_rate_year');
                $table->dropForeignIdFor('flight_season');
                $table->dropForeignIdFor('flight_rate_type');
                $table->dropColumn([
                    'flight_rate_year_id',
                    'flight_season_id',
                    'flight_rate_type_id',
                    'base_adult_price',
                    'base_child_price',
                    'base_guide_price',
                    'is_active',
                ]);
            });
        }

        Schema::dropIfExists('flight_rate_types');
        Schema::dropIfExists('flight_seasons');
        Schema::dropIfExists('flight_rate_years');
    }
};
