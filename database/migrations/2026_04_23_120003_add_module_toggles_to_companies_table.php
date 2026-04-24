<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'enable_flights')) {
                $table->boolean('enable_flights')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('companies', 'enable_transport')) {
                $table->boolean('enable_transport')->default(true)->after('enable_flights');
            }
            if (!Schema::hasColumn('companies', 'enable_activities')) {
                $table->boolean('enable_activities')->default(true)->after('enable_transport');
            }
            if (!Schema::hasColumn('companies', 'enable_advanced_rates')) {
                $table->boolean('enable_advanced_rates')->default(true)->after('enable_activities');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $columns = [
                'enable_flights',
                'enable_transport',
                'enable_activities',
                'enable_advanced_rates',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
