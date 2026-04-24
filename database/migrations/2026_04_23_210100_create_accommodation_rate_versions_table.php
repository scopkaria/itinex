<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodation_rate_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->decimal('old_value', 12, 2)->nullable();
            $table->decimal('new_value', 12, 2)->nullable();
            $table->string('change_type');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'created_at'], 'arv_entity_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_rate_versions');
    }
};
