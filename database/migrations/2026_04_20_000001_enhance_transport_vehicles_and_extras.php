<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance provider_vehicles with full vehicle spec fields
        Schema::table('provider_vehicles', function (Blueprint $table) {
            $columns = Schema::getColumnListing('provider_vehicles');

            if (!in_array('manufacturer', $columns)) {
                $table->string('manufacturer')->nullable()->after('make_model');
            }
            if (!in_array('model', $columns)) {
                $table->string('model')->nullable()->after('manufacturer');
            }
            if (!in_array('branch', $columns)) {
                $table->string('branch')->nullable()->after('model');
            }
            if (!in_array('driver_id', $columns)) {
                $table->foreignId('driver_id')->nullable()->after('branch')
                      ->constrained('transport_drivers')->nullOnDelete();
            }
            if (!in_array('fuel_type', $columns)) {
                $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid'])->default('diesel')->after('driver_id');
            }
            if (!in_array('engine_number', $columns)) {
                $table->string('engine_number')->nullable()->after('fuel_type');
            }
            if (!in_array('chassis_number', $columns)) {
                $table->string('chassis_number')->nullable()->after('engine_number');
            }
            if (!in_array('seats', $columns)) {
                $table->unsignedSmallInteger('seats')->nullable()->after('chassis_number');
            }
            if (!in_array('scope', $columns)) {
                $table->enum('scope', ['safari', 'transfer', 'both'])->default('both')->after('seats');
            }
            if (!in_array('fuel_consumption', $columns)) {
                $table->decimal('fuel_consumption', 6, 2)->nullable()->after('scope');
            }
        });

        // Transport documents table
        if (!Schema::hasTable('transport_documents')) {
            Schema::create('transport_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('type')->default('general'); // license, insurance, permit, registration, general
                $table->string('file_path');
                $table->string('file_name');
                $table->date('expiry_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Enhance extras table for miscellaneous module
        Schema::table('extras', function (Blueprint $table) {
            $columns = Schema::getColumnListing('extras');

            if (!in_array('category', $columns)) {
                $table->string('category')->nullable()->after('name');
            }
            if (!in_array('description', $columns)) {
                $table->text('description')->nullable()->after('category');
            }
            if (!in_array('unit', $columns)) {
                $table->string('unit')->default('per_person')->after('price'); // per_person, per_group, flat
            }
            if (!in_array('is_active', $columns)) {
                $table->boolean('is_active')->default(true)->after('unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('provider_vehicles', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn([
                'manufacturer', 'model', 'branch', 'driver_id', 'fuel_type',
                'engine_number', 'chassis_number', 'seats', 'scope', 'fuel_consumption',
            ]);
        });

        Schema::dropIfExists('transport_documents');

        Schema::table('extras', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'unit', 'is_active']);
        });
    }
};
