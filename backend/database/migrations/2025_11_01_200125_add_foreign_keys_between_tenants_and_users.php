<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔹 Adiciona FK de tenant_id → tenants.id (em users)
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasTable('tenants') && !Schema::hasColumn('users', 'tenant_id_foreign')) {
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->cascadeOnDelete();
            }
        });

        // 🔹 Adiciona FK de owner_user_id → users.id (em tenants)
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasTable('users') && !Schema::hasColumn('tenants', 'owner_user_id_foreign')) {
                $table->foreign('owner_user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
        });
    }
};
