<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand the type enum to include 'flight'
        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->enum('type', ['hotel', 'transport', 'park_fee', 'activity', 'extra', 'flight'])
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->enum('type', ['hotel', 'transport', 'park_fee', 'activity', 'extra'])
                ->change();
        });
    }
};
