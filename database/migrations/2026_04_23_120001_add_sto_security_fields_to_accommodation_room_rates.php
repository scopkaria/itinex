<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_room_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_room_rates', 'sto_rate_raw')) {
                $table->text('sto_rate_raw')->nullable()->after('rate_type_id');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'contracted_rate')) {
                $table->decimal('contracted_rate', 12, 2)->nullable()->after('sto_rate_raw');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'promotional_rate')) {
                $table->decimal('promotional_rate', 12, 2)->nullable()->after('contracted_rate');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'derived_rate')) {
                $table->decimal('derived_rate', 12, 2)->nullable()->after('promotional_rate');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'visibility_mode')) {
                $table->enum('visibility_mode', ['private', 'computed'])->default('private')->after('derived_rate');
            }
            if (!Schema::hasColumn('accommodation_room_rates', 'rate_source')) {
                $table->string('rate_source', 30)->default('manual')->after('visibility_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_room_rates', function (Blueprint $table) {
            $columns = [
                'sto_rate_raw',
                'contracted_rate',
                'promotional_rate',
                'derived_rate',
                'visibility_mode',
                'rate_source',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('accommodation_room_rates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
