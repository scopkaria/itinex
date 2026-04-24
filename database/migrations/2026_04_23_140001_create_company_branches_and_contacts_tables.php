<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('vrn')->nullable();
            $table->string('tin')->nullable();
            $table->text('banking_details')->nullable();
            $table->string('source_code', 50)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('company_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('category')->nullable();
            $table->string('contact_type')->nullable();
            $table->string('company_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email_work')->nullable();
            $table->string('email_personal')->nullable();
            $table->string('phone_business')->nullable();
            $table->string('phone_mobile')->nullable();
            $table->string('country')->nullable();
            $table->string('website')->nullable();
            $table->decimal('markup', 8, 2)->default(0);
            $table->text('elements')->nullable();
            $table->json('metadata')->nullable();
            $table->string('source_code', 50)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'company_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_contacts');
        Schema::dropIfExists('company_branches');
    }
};
