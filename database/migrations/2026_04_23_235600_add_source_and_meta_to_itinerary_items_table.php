<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itinerary_items', function (Blueprint $table) {
            if (! Schema::hasColumn('itinerary_items', 'reference_source')) {
                $table->string('reference_source', 60)->nullable()->after('reference_id');
            }

            if (! Schema::hasColumn('itinerary_items', 'meta')) {
                $table->json('meta')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('itinerary_items', function (Blueprint $table) {
            if (Schema::hasColumn('itinerary_items', 'reference_source')) {
                $table->dropColumn('reference_source');
            }

            if (Schema::hasColumn('itinerary_items', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
