<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_pricing_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('itinerary_id')->constrained('itineraries')->cascadeOnDelete();
            $table->enum('partner_type', ['agent', 'partner']);
            $table->string('partner_key', 120);
            $table->enum('override_mode', ['percent', 'fixed']);
            $table->decimal('override_value', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['itinerary_id', 'partner_type', 'partner_key'], 'itinerary_partner_unique');
            $table->index(['company_id', 'partner_type', 'partner_key'], 'ipo_company_partner_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_pricing_overrides');
    }
};
