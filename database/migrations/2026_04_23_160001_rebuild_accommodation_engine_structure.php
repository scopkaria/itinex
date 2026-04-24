<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'accommodation_company_edit_enabled')) {
                $table->boolean('accommodation_company_edit_enabled')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('companies', 'accommodation_company_sto_edit_enabled')) {
                $table->boolean('accommodation_company_sto_edit_enabled')->default(false)->after('accommodation_company_edit_enabled');
            }
        });

        Schema::table('accommodation_rate_types', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_rate_types', 'code')) {
                $table->string('code', 30)->nullable()->after('name');
            }
            if (!Schema::hasColumn('accommodation_rate_types', 'markup_percent')) {
                $table->decimal('markup_percent', 8, 2)->default(0)->after('description');
            }
            if (!Schema::hasColumn('accommodation_rate_types', 'markup_fixed')) {
                $table->decimal('markup_fixed', 12, 2)->default(0)->after('markup_percent');
            }
        });

        Schema::table('accommodation_seasons', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_seasons', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('rate_year_id')->constrained('destinations')->nullOnDelete();
            }
        });

        Schema::table('room_types', function (Blueprint $table) {
            if (!Schema::hasColumn('room_types', 'label')) {
                $table->string('label')->nullable()->after('type');
            }
            if (!Schema::hasColumn('room_types', 'max_adults')) {
                $table->unsignedTinyInteger('max_adults')->default(2)->after('label');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE room_types MODIFY type VARCHAR(50) NOT NULL');
        }

        Schema::table('meal_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('meal_plans', 'abbreviation')) {
                $table->string('abbreviation', 10)->nullable()->after('name');
            }
            if (!Schema::hasColumn('meal_plans', 'full_name')) {
                $table->string('full_name')->nullable()->after('abbreviation');
            }
            if (!Schema::hasColumn('meal_plans', 'description_i18n')) {
                $table->json('description_i18n')->nullable()->after('full_name');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE meal_plans MODIFY name VARCHAR(20) NOT NULL');
        }

        Schema::table('accommodation_room_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_room_rates', 'rate_kind')) {
                $table->string('rate_kind', 20)->default('sto')->after('rate_type_id');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'markup_percent')) {
                $table->decimal('markup_percent', 8, 2)->default(0)->after('rate_kind');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'markup_fixed')) {
                $table->decimal('markup_fixed', 12, 2)->default(0)->after('markup_percent');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'per_person_sharing_double')) {
                $table->decimal('per_person_sharing_double', 12, 2)->default(0)->after('single_supplement');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'per_person_sharing_twin')) {
                $table->decimal('per_person_sharing_twin', 12, 2)->default(0)->after('per_person_sharing_double');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'triple_adjustment')) {
                $table->decimal('triple_adjustment', 12, 2)->default(0)->after('per_person_sharing_twin');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'is_override')) {
                $table->boolean('is_override')->default(false)->after('visibility_mode');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE accommodation_room_rates MODIFY visibility_mode ENUM('private', 'computed', 'computed_only') NOT NULL DEFAULT 'private'");
        }

        DB::table('room_types')->whereNull('label')->update(['label' => DB::raw('type')]);

        $defaults = [
            ['name' => 'single', 'label' => 'Single', 'max_adults' => 1],
            ['name' => 'double', 'label' => 'Double', 'max_adults' => 2],
            ['name' => 'twin', 'label' => 'Twin', 'max_adults' => 2],
            ['name' => 'twin_single', 'label' => 'Twin + Single', 'max_adults' => 3],
            ['name' => 'triple', 'label' => 'Triple', 'max_adults' => 3],
            ['name' => 'quadruple', 'label' => 'Quadruple', 'max_adults' => 4],
            ['name' => 'quintuple', 'label' => 'Quintuple', 'max_adults' => 5],
            ['name' => 'family', 'label' => 'Family', 'max_adults' => 6],
        ];

        $hotelIds = DB::table('hotels')->pluck('id');
        foreach ($hotelIds as $hotelId) {
            foreach ($defaults as $type) {
                $exists = DB::table('room_types')
                    ->where('hotel_id', $hotelId)
                    ->where('type', $type['name'])
                    ->exists();

                if ($exists) {
                    DB::table('room_types')
                        ->where('hotel_id', $hotelId)
                        ->where('type', $type['name'])
                        ->update(['label' => $type['label'], 'max_adults' => $type['max_adults']]);
                    continue;
                }

                DB::table('room_types')->insert([
                    'hotel_id' => $hotelId,
                    'type' => $type['name'],
                    'label' => $type['label'],
                    'max_adults' => $type['max_adults'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $mealDefaults = [
            ['abbr' => 'BB', 'full_name' => 'Bed and Breakfast'],
            ['abbr' => 'HB', 'full_name' => 'Half Board'],
            ['abbr' => 'FB', 'full_name' => 'Full Board'],
            ['abbr' => 'AI', 'full_name' => 'All Inclusive'],
        ];

        if (DB::getDriverName() === 'mysql') {
            $mealDefaults[] = ['abbr' => 'GP', 'full_name' => 'Game Package'];
        }

        foreach ($mealDefaults as $meal) {
            $row = DB::table('meal_plans')->where('name', $meal['abbr'])->first();
            if ($row) {
                DB::table('meal_plans')->where('id', $row->id)->update([
                    'abbreviation' => $meal['abbr'],
                    'full_name' => $meal['full_name'],
                    'description_i18n' => json_encode(['en' => $meal['full_name']]),
                ]);
                continue;
            }

            DB::table('meal_plans')->insert([
                'name' => $meal['abbr'],
                'abbreviation' => $meal['abbr'],
                'full_name' => $meal['full_name'],
                'description_i18n' => json_encode(['en' => $meal['full_name']]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasColumn('accommodation_room_rates', 'rate_uniqueness_guard')) {
            Schema::table('accommodation_room_rates', function (Blueprint $table) {
                $table->string('rate_uniqueness_guard')->nullable()->after('currency');
            });

            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                DB::statement(
                    "UPDATE accommodation_room_rates SET rate_uniqueness_guard = (
                        IFNULL(rate_year_id,0) || ':' ||
                        IFNULL(season_id,0) || ':' ||
                        IFNULL(room_type_id,0) || ':' ||
                        IFNULL(meal_plan_id,0) || ':' ||
                        IFNULL(rate_type_id,0)
                    )"
                );
            } elseif ($driver === 'pgsql') {
                DB::statement(
                    "UPDATE accommodation_room_rates SET rate_uniqueness_guard = (
                        COALESCE(rate_year_id,0)::text || ':' ||
                        COALESCE(season_id,0)::text || ':' ||
                        COALESCE(room_type_id,0)::text || ':' ||
                        COALESCE(meal_plan_id,0)::text || ':' ||
                        COALESCE(rate_type_id,0)::text
                    )"
                );
            } else {
                DB::statement(
                    "UPDATE accommodation_room_rates SET rate_uniqueness_guard = CONCAT(
                        IFNULL(rate_year_id,0), ':',
                        IFNULL(season_id,0), ':',
                        IFNULL(room_type_id,0), ':',
                        IFNULL(meal_plan_id,0), ':',
                        IFNULL(rate_type_id,0)
                    )"
                );
            }

            Schema::table('accommodation_room_rates', function (Blueprint $table) {
                $table->unique(['hotel_id', 'rate_uniqueness_guard'], 'uniq_hotel_rate_matrix');
            });
        }
    }

    public function down(): void
    {
        Schema::table('accommodation_room_rates', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_room_rates', 'rate_uniqueness_guard')) {
                $table->dropUnique('uniq_hotel_rate_matrix');
                $table->dropColumn('rate_uniqueness_guard');
            }

            $drop = [
                'rate_kind',
                'markup_percent',
                'markup_fixed',
                'per_person_sharing_double',
                'per_person_sharing_twin',
                'triple_adjustment',
                'is_override',
            ];

            foreach ($drop as $column) {
                if (Schema::hasColumn('accommodation_room_rates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('meal_plans', function (Blueprint $table) {
            foreach (['abbreviation', 'full_name', 'description_i18n'] as $column) {
                if (Schema::hasColumn('meal_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('room_types', function (Blueprint $table) {
            foreach (['label', 'max_adults'] as $column) {
                if (Schema::hasColumn('room_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('accommodation_seasons', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_seasons', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }
        });

        Schema::table('accommodation_rate_types', function (Blueprint $table) {
            foreach (['code', 'markup_percent', 'markup_fixed'] as $column) {
                if (Schema::hasColumn('accommodation_rate_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            foreach (['accommodation_company_edit_enabled', 'accommodation_company_sto_edit_enabled'] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
