<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Which import run produced this record
            $table->string('import_source', 100);        // e.g. the original filename
            $table->string('source_table', 100);          // legacy table name, e.g. flight_rates
            $table->unsignedInteger('source_row_index');  // 0-based position in that INSERT batch

            // Disposition
            $table->enum('status', ['accepted', 'rejected', 'skipped'])
                  ->default('accepted')->index();
            $table->json('violations')->nullable();       // array of validation error strings
            $table->json('raw_row')->nullable();          // original parsed row values for debugging

            // What was created (nullable; only set for accepted rows)
            $table->string('target_model')->nullable();   // e.g. App\Models\MasterData\FlightSeasonalRate
            $table->unsignedBigInteger('target_id')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'source_table', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_audit');
    }
};
