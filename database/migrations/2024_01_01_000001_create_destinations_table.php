<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('country');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
