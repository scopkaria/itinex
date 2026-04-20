<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['single', 'double', 'triple']);
            $table->timestamps();

            $table->unique(['hotel_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
