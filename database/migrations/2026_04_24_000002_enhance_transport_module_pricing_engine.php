<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transport Rate Years
        if (! Schema::hasTable('transport_rate_years')) {
            Schema::create('transport_rate_years', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->year('year');
                $table->date('valid_from');
                $table->date('valid_to');
                $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
                $table->timestamps();
                $table->unique(['transport_provider_id', 'year']);
            });
        }

        // Transport Seasons
        if (! Schema::hasTable('transport_seasons')) {
            Schema::create('transport_seasons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('transport_rate_year_id')->nullable()->constrained('transport_rate_years')->nullOnDelete();
                $table->string('name'); // High, Low, Peak
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        // Transport Rate Types (STO, Contract, Custom)
        if (! Schema::hasTable('transport_rate_types')) {
            Schema::create('transport_rate_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // STO, Contract, Custom
                $table->decimal('markup_percentage', 8, 2)->default(0);
                $table->decimal('markup_fixed', 12, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Enhanced Transfer Routes (link to destinations)
        if (Schema::hasTable('transfer_routes')) {
            Schema::table('transfer_routes', function (Blueprint $table) {
                if (! Schema::hasColumn('transfer_routes', 'origin_destination_id')) {
                    $table->foreignId('origin_destination_id')->nullable()->constrained('destinations')->nullOnDelete();
                }
                if (! Schema::hasColumn('transfer_routes', 'arrival_destination_id')) {
                    $table->foreignId('arrival_destination_id')->nullable()->constrained('destinations')->nullOnDelete();
                }
                if (Schema::hasColumn('transfer_routes', 'origin')) {
                    $table->dropColumn(['origin', 'destination']);
                }
            });
        }

        // Enhanced Transport Drivers with comprehensive info
        Schema::table('transport_drivers', function (Blueprint $table) {
            if (! Schema::hasColumn('transport_drivers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (! Schema::hasColumn('transport_drivers', 'employment_date')) {
                $table->date('employment_date')->nullable();
            }
            if (! Schema::hasColumn('transport_drivers', 'license_type')) {
                $table->string('license_type')->nullable();
            }
            if (! Schema::hasColumn('transport_drivers', 'license_expiry')) {
                $table->date('license_expiry')->nullable();
            }
            if (! Schema::hasColumn('transport_drivers', 'skill_level')) {
                $table->enum('skill_level', ['beginner', 'pro', 'expert'])->default('pro');
            }
            if (! Schema::hasColumn('transport_drivers', 'languages')) {
                $table->json('languages')->nullable();
            }
            if (! Schema::hasColumn('transport_drivers', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('transport_drivers', 'vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->constrained('provider_vehicles')->nullOnDelete();
            }
        });

        // Enhanced Provider Vehicles with more details
        Schema::table('provider_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('provider_vehicles', 'engine_number')) {
                $table->string('engine_number')->nullable();
            }
            if (! Schema::hasColumn('provider_vehicles', 'chassis_number')) {
                $table->string('chassis_number')->nullable();
            }
            if (! Schema::hasColumn('provider_vehicles', 'fuel_type')) {
                $table->string('fuel_type')->nullable();
            }
            if (! Schema::hasColumn('provider_vehicles', 'fuel_consumption_kmpl')) {
                $table->decimal('fuel_consumption_kmpl', 5, 2)->nullable();
            }
            if (! Schema::hasColumn('provider_vehicles', 'scope')) {
                $table->string('scope')->default('both');
            }
            if (! Schema::hasColumn('provider_vehicles', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        // Transport Transfer Rates (matrix: Route x Vehicle Type)
        if (! Schema::hasTable('transport_transfer_rates')) {
            Schema::create('transport_transfer_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('transfer_route_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_type_id')->constrained()->cascadeOnDelete();
                $table->foreignId('transport_season_id')->nullable()->constrained('transport_seasons')->nullOnDelete();
                $table->decimal('buy_price', 12, 2)->default(0);
                $table->decimal('sell_price', 12, 2)->default(0);
                $table->string('rate_type')->default('per_transfer');
                $table->timestamps();
                $table->unique(['transport_provider_id', 'transfer_route_id', 'vehicle_type_id', 'transport_season_id'], 'ttr_provider_route_vehicle_season_unique');
            });
        }

        // Empty Run Rates
        if (! Schema::hasTable('transport_empty_run_rates')) {
            Schema::create('transport_empty_run_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('transfer_route_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_type_id')->constrained()->cascadeOnDelete();
                $table->decimal('rate', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Enhanced Transport Cost Settings
        if (Schema::hasTable('transport_cost_settings')) {
            Schema::table('transport_cost_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('transport_cost_settings', 'currency')) {
                    $table->string('currency', 3)->default('TZS');
                }
                if (! Schema::hasColumn('transport_cost_settings', 'updated_at')) {
                    $table->timestamps();
                }
            });
        }

        // Transport Imprest Components
        if (! Schema::hasTable('transport_imprest_components')) {
            Schema::create('transport_imprest_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->enum('type', ['daily', 'per_vehicle', 'per_km', 'fixed'])->default('fixed');
                $table->decimal('cost', 12, 2);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Transport Vehicle Descriptions
        if (! Schema::hasTable('transport_vehicle_descriptions')) {
            Schema::create('transport_vehicle_descriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_type_id')->constrained()->cascadeOnDelete();
                $table->string('language', 2)->default('en');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->unique(['transport_provider_id', 'vehicle_type_id', 'language'], 'tvd_provider_type_lang_unique');
            });
        }

        // Transport Payment Policies
        if (! Schema::hasTable('transport_payment_policies')) {
            Schema::create('transport_payment_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->integer('days_before_arrival');
                $table->decimal('percentage_due', 5, 2);
                $table->timestamps();
            });
        }

        // Transport Cancellation Policies
        if (! Schema::hasTable('transport_cancellation_policies')) {
            Schema::create('transport_cancellation_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('transport_season_id')->nullable()->constrained('transport_seasons')->nullOnDelete();
                $table->integer('days_before_travel');
                $table->decimal('penalty_percentage', 5, 2);
                $table->timestamps();
            });
        }

        // Transport Rate History/Versioning
        if (! Schema::hasTable('transport_rate_versions')) {
            Schema::create('transport_rate_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id');
                $table->decimal('old_value', 12, 2)->nullable();
                $table->decimal('new_value', 12, 2)->nullable();
                $table->string('change_type');
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['entity_type', 'entity_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_rate_versions');
        Schema::dropIfExists('transport_cancellation_policies');
        Schema::dropIfExists('transport_payment_policies');
        Schema::dropIfExists('transport_vehicle_descriptions');
        Schema::dropIfExists('transport_imprest_components');
        Schema::dropIfExists('transport_empty_run_rates');
        Schema::dropIfExists('transport_transfer_rates');

        // Drop new columns from existing tables
        if (Schema::hasTable('provider_vehicles')) {
            Schema::table('provider_vehicles', function (Blueprint $table) {
                $table->dropColumn(['engine_number', 'chassis_number', 'fuel_type', 'fuel_consumption_kmpl', 'scope', 'notes']);
            });
        }

        if (Schema::hasTable('transport_drivers')) {
            Schema::table('transport_drivers', function (Blueprint $table) {
                $table->dropForeignIdFor('vehicle');
                $table->dropColumn([
                    'date_of_birth',
                    'employment_date',
                    'license_type',
                    'license_expiry',
                    'skill_level',
                    'languages',
                    'notes',
                    'vehicle_id',
                ]);
            });
        }

        if (Schema::hasTable('transfer_routes')) {
            Schema::table('transfer_routes', function (Blueprint $table) {
                $table->dropForeignIdFor('origin_destination');
                $table->dropForeignIdFor('arrival_destination');
                $table->dropColumn([
                    'origin_destination_id',
                    'arrival_destination_id',
                ]);
                $table->string('origin')->nullable();
                $table->string('destination')->nullable();
            });
        }

        if (Schema::hasTable('transport_cost_settings')) {
            Schema::table('transport_cost_settings', function (Blueprint $table) {
                $table->dropColumn(['currency', 'timestamps']);
            });
        }

        Schema::dropIfExists('transport_rate_types');
        Schema::dropIfExists('transport_seasons');
        Schema::dropIfExists('transport_rate_years');
    }
};
