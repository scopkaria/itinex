<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('module', 50);
            $table->unsignedBigInteger('provider_id')->nullable()->index();
            $table->string('provider_type', 120)->nullable();
            $table->string('entity_type', 120);
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->string('action', 30);
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable()->index();
            $table->string('source', 30)->default('web');
            $table->timestamps();

            $table->index(['module', 'entity_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_audit_logs');
    }
};
