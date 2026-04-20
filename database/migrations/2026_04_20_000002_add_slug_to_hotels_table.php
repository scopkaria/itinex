<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing('hotels');
        Schema::table('hotels', function (Blueprint $table) use ($columns) {
            if (!in_array('slug', $columns)) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });
    }

    public function down(): void
    {
        $columns = Schema::getColumnListing('hotels');
        if (in_array('slug', $columns)) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
