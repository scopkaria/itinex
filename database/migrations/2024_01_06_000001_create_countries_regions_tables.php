<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 3)->unique();
            $table->string('continent', 50)->default('Africa');
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['country_id', 'name']);
        });

        // Add country_id and region_id to destinations, migrate data, then drop old text columns
        Schema::table('destinations', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('company_id');
            $table->foreignId('region_id')->nullable()->after('country_id');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['country_id', 'region_id']);
        });

        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }
};
