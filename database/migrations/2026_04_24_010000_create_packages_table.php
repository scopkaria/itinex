<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('destination_id')->nullable()->constrained('destinations')->nullOnDelete();
                $table->string('name');
                $table->string('code', 60)->nullable();
                $table->unsignedInteger('nights')->default(1);
                $table->enum('price_mode', ['per_person', 'per_group'])->default('per_person');
                $table->decimal('base_price', 12, 2)->default(0);
                $table->decimal('markup_percentage', 8, 2)->default(0);
                $table->enum('discount_mode', ['none', 'percent', 'fixed'])->default('none');
                $table->decimal('discount_value', 12, 2)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->boolean('is_active')->default(true);
                $table->json('inclusions')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'code']);
                $table->index(['company_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
