<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 120);
            $table->string('email', 120)->nullable();
            $table->string('phone', 20)->nullable();
            $table->date('birthdate')->nullable();
            $table->boolean('consent_marketing')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // 🔗 Relacionamento com Tenant
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
