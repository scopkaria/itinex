<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand hotels table to become accommodations
        $columns = Schema::getColumnListing('hotels');
        Schema::table('hotels', function (Blueprint $table) use ($columns) {
            if (!in_array('chain', $columns)) $table->string('chain')->nullable()->after('name');
            if (!in_array('contact_person', $columns)) $table->string('contact_person')->nullable()->after('category');
            if (!in_array('phone', $columns)) $table->string('phone')->nullable();
            if (!in_array('email', $columns)) $table->string('email')->nullable();
            if (!in_array('website', $columns)) $table->string('website')->nullable();
            if (!in_array('address', $columns)) $table->text('address')->nullable();
            if (!in_array('vat_type', $columns)) $table->string('vat_type', 20)->default('inclusive');
            if (!in_array('markup', $columns)) $table->decimal('markup', 8, 2)->default(0);
            if (!in_array('description', $columns)) $table->text('description')->nullable();
            if (!in_array('is_active', $columns)) $table->boolean('is_active')->default(true);
        });

        // Room Categories (Standard, Deluxe, Suite, etc.)
        Schema::create('room_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Accommodation Media (gallery)
        Schema::create('accommodation_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('file_path');
            $table->boolean('is_cover')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Rate Years
        Schema::create('accommodation_rate_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->integer('year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['hotel_id', 'year']);
        });

        // Seasons
        Schema::create('accommodation_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_year_id')->constrained('accommodation_rate_years')->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        // Rate Types (rack, contract, promo, etc.)
        Schema::create('accommodation_rate_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Room Rates (the main price matrix)
        Schema::create('accommodation_room_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('rate_year_id')->constrained('accommodation_rate_years')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('accommodation_seasons')->cascadeOnDelete();
            $table->foreignId('room_category_id')->nullable()->constrained('room_categories')->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->foreignId('meal_plan_id')->nullable()->constrained('meal_plans')->nullOnDelete();
            $table->foreignId('rate_type_id')->nullable()->constrained('accommodation_rate_types')->nullOnDelete();
            $table->decimal('adult_rate', 12, 2)->default(0);
            $table->decimal('child_rate', 12, 2)->default(0);
            $table->decimal('infant_rate', 12, 2)->default(0);
            $table->decimal('single_supplement', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });

        // Extra Fees (e.g. Christmas supplement)
        Schema::create('accommodation_extra_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('rate_year_id')->nullable()->constrained('accommodation_rate_years')->nullOnDelete();
            $table->string('name');
            $table->string('fee_type')->default('per_person'); // per_person, per_room, flat
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Holiday Supplements
        Schema::create('accommodation_holiday_supplements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('rate_year_id')->nullable()->constrained('accommodation_rate_years')->nullOnDelete();
            $table->string('holiday_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('supplement_amount', 12, 2)->default(0);
            $table->string('apply_to')->default('per_person'); // per_person, per_room
            $table->timestamps();
        });

        // Accommodation Activities
        Schema::create('accommodation_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_person', 12, 2)->default(0);
            $table->timestamps();
        });

        // Child Policy
        Schema::create('accommodation_child_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->integer('min_age')->default(0);
            $table->integer('max_age')->default(17);
            $table->string('policy_type')->default('percentage'); // percentage, fixed, free
            $table->decimal('value', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Payment Policy
        Schema::create('accommodation_payment_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        // Cancellation Policy
        Schema::create('accommodation_cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->integer('days_before');
            $table->decimal('penalty_percentage', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tour Leader Discount
        Schema::create('accommodation_tour_leader_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->integer('min_pax')->default(1);
            $table->string('discount_type')->default('free'); // free, percentage, fixed
            $table->decimal('value', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Backup Rates
        Schema::create('accommodation_backup_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('label');
            $table->json('rate_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_backup_rates');
        Schema::dropIfExists('accommodation_tour_leader_discounts');
        Schema::dropIfExists('accommodation_cancellation_policies');
        Schema::dropIfExists('accommodation_payment_policies');
        Schema::dropIfExists('accommodation_child_policies');
        Schema::dropIfExists('accommodation_activities');
        Schema::dropIfExists('accommodation_holiday_supplements');
        Schema::dropIfExists('accommodation_extra_fees');
        Schema::dropIfExists('accommodation_room_rates');
        Schema::dropIfExists('accommodation_rate_types');
        Schema::dropIfExists('accommodation_seasons');
        Schema::dropIfExists('accommodation_rate_years');
        Schema::dropIfExists('accommodation_media');
        Schema::dropIfExists('room_categories');

        Schema::table('hotels', function (Blueprint $table) {
            $cols = Schema::getColumnListing('hotels');
            $drop = array_intersect(['chain', 'contact_person', 'phone', 'email', 'website', 'address', 'vat_type', 'markup', 'description', 'is_active'], $cols);
            if (!empty($drop)) $table->dropColumn($drop);
        });
    }
};
