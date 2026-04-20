<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. destinations.view, accommodations.edit
            $table->string('group'); // e.g. destinations, accommodations, flights
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // User permissions pivot
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'permission_id']);
        });

        // Company module toggles
        Schema::create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('module'); // destinations, accommodations, flights, transport, itineraries
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_modules');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('permissions');
    }
};
