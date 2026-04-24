<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_extra_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_extra_fees', 'adult_rate')) {
                $table->decimal('adult_rate', 12, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('accommodation_extra_fees', 'child_rate')) {
                $table->decimal('child_rate', 12, 2)->default(0)->after('adult_rate');
            }
            if (!Schema::hasColumn('accommodation_extra_fees', 'resident_rate')) {
                $table->decimal('resident_rate', 12, 2)->default(0)->after('child_rate');
            }
            if (!Schema::hasColumn('accommodation_extra_fees', 'non_resident_rate')) {
                $table->decimal('non_resident_rate', 12, 2)->default(0)->after('resident_rate');
            }
            if (!Schema::hasColumn('accommodation_extra_fees', 'apply_per')) {
                $table->enum('apply_per', ['person', 'vehicle', 'group'])->default('person')->after('non_resident_rate');
            }
            if (!Schema::hasColumn('accommodation_extra_fees', 'valid_from')) {
                $table->date('valid_from')->nullable()->after('apply_per');
            }
            if (!Schema::hasColumn('accommodation_extra_fees', 'valid_to')) {
                $table->date('valid_to')->nullable()->after('valid_from');
            }
        });

        Schema::table('accommodation_holiday_supplements', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_holiday_supplements', 'supplement_date')) {
                $table->date('supplement_date')->nullable()->after('holiday_name');
            }
            if (!Schema::hasColumn('accommodation_holiday_supplements', 'adult_rate')) {
                $table->decimal('adult_rate', 12, 2)->default(0)->after('supplement_amount');
            }
            if (!Schema::hasColumn('accommodation_holiday_supplements', 'child_rate')) {
                $table->decimal('child_rate', 12, 2)->default(0)->after('adult_rate');
            }
            if (!Schema::hasColumn('accommodation_holiday_supplements', 'room_type_id')) {
                $table->foreignId('room_type_id')->nullable()->after('rate_year_id')->constrained('room_types')->nullOnDelete();
            }
        });

        Schema::table('accommodation_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_activities', 'rate_adult')) {
                $table->decimal('rate_adult', 12, 2)->default(0)->after('price_per_person');
            }
            if (!Schema::hasColumn('accommodation_activities', 'rate_child')) {
                $table->decimal('rate_child', 12, 2)->default(0)->after('rate_adult');
            }
            if (!Schema::hasColumn('accommodation_activities', 'rate_guide')) {
                $table->decimal('rate_guide', 12, 2)->default(0)->after('rate_child');
            }
            if (!Schema::hasColumn('accommodation_activities', 'rate_vehicle')) {
                $table->decimal('rate_vehicle', 12, 2)->default(0)->after('rate_guide');
            }
            if (!Schema::hasColumn('accommodation_activities', 'rate_group')) {
                $table->decimal('rate_group', 12, 2)->default(0)->after('rate_vehicle');
            }
        });

        Schema::table('accommodation_child_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_child_policies', 'sharing_type')) {
                $table->enum('sharing_type', ['alone', 'with_adult'])->nullable()->after('policy_type');
            }
            if (!Schema::hasColumn('accommodation_child_policies', 'discount_percentage')) {
                $table->decimal('discount_percentage', 8, 2)->default(0)->after('value');
            }
            if (!Schema::hasColumn('accommodation_child_policies', 'discount_fixed')) {
                $table->decimal('discount_fixed', 12, 2)->default(0)->after('discount_percentage');
            }
            if (!Schema::hasColumn('accommodation_child_policies', 'room_type_id')) {
                $table->foreignId('room_type_id')->nullable()->after('hotel_id')->constrained('room_types')->nullOnDelete();
            }
            if (!Schema::hasColumn('accommodation_child_policies', 'meal_plan_id')) {
                $table->foreignId('meal_plan_id')->nullable()->after('room_type_id')->constrained('meal_plans')->nullOnDelete();
            }
            if (!Schema::hasColumn('accommodation_child_policies', 'season_id')) {
                $table->foreignId('season_id')->nullable()->after('meal_plan_id')->constrained('accommodation_seasons')->nullOnDelete();
            }
        });

        Schema::table('accommodation_payment_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_payment_policies', 'days_before')) {
                $table->integer('days_before')->nullable()->after('title');
            }
            if (!Schema::hasColumn('accommodation_payment_policies', 'percentage')) {
                $table->decimal('percentage', 8, 2)->default(0)->after('days_before');
            }
        });

        Schema::table('accommodation_tour_leader_discounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_tour_leader_discounts', 'max_pax')) {
                $table->integer('max_pax')->nullable()->after('min_pax');
            }
            if (!Schema::hasColumn('accommodation_tour_leader_discounts', 'discount_percentage')) {
                $table->decimal('discount_percentage', 8, 2)->default(0)->after('value');
            }
        });

        Schema::table('accommodation_backup_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_backup_rates', 'version_no')) {
                $table->unsignedInteger('version_no')->default(1)->after('hotel_id');
            }
            if (!Schema::hasColumn('accommodation_backup_rates', 'snapshot_date')) {
                $table->date('snapshot_date')->nullable()->after('label');
            }
            if (!Schema::hasColumn('accommodation_backup_rates', 'source_rate_year_id')) {
                $table->foreignId('source_rate_year_id')->nullable()->after('snapshot_date')->constrained('accommodation_rate_years')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Keep down migration non-destructive for production safety.
    }
};
