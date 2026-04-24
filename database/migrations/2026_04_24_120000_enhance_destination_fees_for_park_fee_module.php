<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destination_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('destination_fees', 'name')) {
                $table->string('name')->nullable()->after('destination_id');
            }
            if (!Schema::hasColumn('destination_fees', 'supplier')) {
                $table->string('supplier')->nullable()->after('name');
            }
            if (!Schema::hasColumn('destination_fees', 'region')) {
                $table->string('region')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('destination_fees', 'season_id')) {
                $table->unsignedInteger('season_id')->nullable()->after('fee_type');
            }
            if (!Schema::hasColumn('destination_fees', 'markup_type')) {
                $table->enum('markup_type', ['percent', 'fixed'])->default('percent')->after('guide_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('destination_fees', function (Blueprint $table) {
            foreach (['name', 'supplier', 'region', 'season_id', 'markup_type'] as $column) {
                if (Schema::hasColumn('destination_fees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
