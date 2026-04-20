<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destination_fees', function (Blueprint $table) {
            $table->string('fee_type', 100)->default('Park Fee')->after('destination_id');

            $table->renameColumn('season', 'season_name');
            $table->renameColumn('non_resident_adult', 'nr_adult');
            $table->renameColumn('non_resident_child', 'nr_child');
            $table->renameColumn('vehicle_fee', 'vehicle_rate');
            $table->renameColumn('guide_fee', 'guide_rate');
        });
    }

    public function down(): void
    {
        Schema::table('destination_fees', function (Blueprint $table) {
            $table->dropColumn('fee_type');

            $table->renameColumn('season_name', 'season');
            $table->renameColumn('nr_adult', 'non_resident_adult');
            $table->renameColumn('nr_child', 'non_resident_child');
            $table->renameColumn('vehicle_rate', 'vehicle_fee');
            $table->renameColumn('guide_rate', 'guide_fee');
        });
    }
};
