<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'subscription_plan')) {
                $table->string('subscription_plan', 50)->default('starter')->after('is_active');
            }
            if (!Schema::hasColumn('companies', 'max_users')) {
                $table->unsignedInteger('max_users')->default(5)->after('subscription_plan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'max_users')) {
                $table->dropColumn('max_users');
            }
            if (Schema::hasColumn('companies', 'subscription_plan')) {
                $table->dropColumn('subscription_plan');
            }
        });
    }
};
