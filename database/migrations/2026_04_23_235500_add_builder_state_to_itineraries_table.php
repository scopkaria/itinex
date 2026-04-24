<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            if (! Schema::hasColumn('itineraries', 'builder_state')) {
                $table->json('builder_state')->nullable()->after('margin_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            if (Schema::hasColumn('itineraries', 'builder_state')) {
                $table->dropColumn('builder_state');
            }
        });
    }
};
