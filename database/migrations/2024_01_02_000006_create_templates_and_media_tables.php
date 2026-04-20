<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('primary_color', 7)->default('#4f46e5');
            $table->string('font')->default('Inter');
            $table->enum('layout_type', ['classic', 'modern', 'minimal'])->default('classic');
            $table->text('footer_text')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('company_id');
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['hotel', 'destination', 'banner', 'logo', 'other']);
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('itinerary_templates');
    }
};
