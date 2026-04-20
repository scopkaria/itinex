<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_active to countries
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('continent');
        });

        // Add is_active + type to regions
        Schema::table('regions', function (Blueprint $table) {
            $table->string('type', 30)->default('region')->after('name'); // region, circuit, zone
            $table->boolean('is_active')->default(true)->after('type');
        });

        // Add lat/lng to destinations for map integration
        Schema::table('destinations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('description');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Company country access pivot (SaaS licensing)
        Schema::create('company_country_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_country_access');

        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_active']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
