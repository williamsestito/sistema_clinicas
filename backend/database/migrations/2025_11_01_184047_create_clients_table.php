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

            // 🔗 Tenant (clínica)
            $table->unsignedBigInteger('tenant_id');

            // 👤 Identificação pessoal
            $table->string('name', 120);
            $table->string('social_name', 120)->nullable();
            $table->boolean('use_social_name')->default(false);
            $table->string('email', 120)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('document', 14)->nullable(); // CPF
            $table->string('rg', 20)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('civil_status', 30)->nullable();

            // 🏠 Endereço
            $table->string('cep', 10)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('number', 10)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();

            // 📋 Preferências e observações
            $table->boolean('consent_marketing')->default(false);
            $table->text('notes')->nullable();

            // ⚙️ Status
            $table->boolean('active')->default(true)->comment('Define se o paciente está ativo ou inativo');

            $table->timestamps();

            // 🔗 Relacionamento com Tenant
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->cascadeOnDelete();

            // 📈 Índices úteis
            $table->index(['tenant_id', 'name']);
            $table->index('email');
            $table->index('phone');
            $table->index('document');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
