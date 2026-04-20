<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destination_fees', function (Blueprint $table) {
            // Add citizen rates
            $table->decimal('citizen_adult', 10, 2)->default(0)->after('resident_child');
            $table->decimal('citizen_child', 10, 2)->default(0)->after('citizen_adult');

            // Replace boolean vat_inclusive with enum vat_type
            $table->string('vat_type', 20)->default('inclusive')->after('guide_fee');

            // Add markup percentage
            $table->decimal('markup', 8, 2)->default(0)->after('vat_type');
        });

        // Migrate existing vat_inclusive boolean to vat_type string
        \Illuminate\Support\Facades\DB::table('destination_fees')
            ->where('vat_inclusive', true)
            ->update(['vat_type' => 'inclusive']);
        \Illuminate\Support\Facades\DB::table('destination_fees')
            ->where('vat_inclusive', false)
            ->update(['vat_type' => 'exclusive']);

        Schema::table('destination_fees', function (Blueprint $table) {
            $table->dropColumn('vat_inclusive');
        });
    }

    public function down(): void
    {
        Schema::table('destination_fees', function (Blueprint $table) {
            $table->boolean('vat_inclusive')->default(true)->after('guide_fee');
        });

        \Illuminate\Support\Facades\DB::table('destination_fees')
            ->where('vat_type', 'inclusive')
            ->update(['vat_inclusive' => true]);
        \Illuminate\Support\Facades\DB::table('destination_fees')
            ->whereIn('vat_type', ['exclusive', 'exempted'])
            ->update(['vat_inclusive' => false]);

        Schema::table('destination_fees', function (Blueprint $table) {
            $table->dropColumn(['citizen_adult', 'citizen_child', 'vat_type', 'markup']);
        });
    }
};
