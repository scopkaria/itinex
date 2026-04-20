<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $table->decimal('markup_percentage', 8, 2)->default(0)->after('profit');
            $table->decimal('margin_percentage', 8, 2)->default(0)->after('markup_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $table->dropColumn(['markup_percentage', 'margin_percentage']);
        });
    }
};
