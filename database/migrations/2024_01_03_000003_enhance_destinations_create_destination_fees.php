<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Enhance destinations table ──
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('region')->nullable()->after('country');
            $table->string('category')->default('national_park')->after('region'); // national_park, conservancy, reserve, marine_park, other
            $table->string('supplier')->nullable()->after('category');
            $table->string('email')->nullable()->after('supplier');
        });

        // ── Redesign park_fees → destination_fees ──
        Schema::create('destination_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('season')->default('Year Round');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            // Non-Resident rates (primary — most tour operators use these)
            $table->decimal('non_resident_adult', 10, 2)->default(0);
            $table->decimal('non_resident_child', 10, 2)->default(0);

            // Resident rates
            $table->decimal('resident_adult', 10, 2)->default(0);
            $table->decimal('resident_child', 10, 2)->default(0);

            // Vehicle & guide
            $table->decimal('vehicle_fee', 10, 2)->default(0);
            $table->decimal('guide_fee', 10, 2)->default(0);

            // VAT handling
            $table->boolean('vat_inclusive')->default(true);

            $table->timestamps();

            $table->index(['company_id', 'destination_id']);
            $table->index(['destination_id', 'season']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_fees');
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['region', 'category', 'supplier', 'email']);
        });
    }
};
