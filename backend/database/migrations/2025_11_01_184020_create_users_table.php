<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // Tenant (clínica)
            $table->unsignedBigInteger('tenant_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | PERFIS DO SISTEMA (APENAS USES INTERNOS)
            |
            | ❌ 'client' removido para evitar conflito com tabela clients
            |--------------------------------------------------------------------------
            */
            $table->enum('role', [
                'owner',
                'admin',
                'professional',
                'frontdesk'
            ])->default('professional');

            // Dados pessoais
            $table->string('name', 120);
            $table->boolean('social_name')->default(false);
            $table->string('social_name_text', 120)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('document', 14)->nullable()->unique(); // CPF
            $table->string('rg', 20)->nullable();
            $table->string('civil_status', 20)->nullable();
            $table->string('gender', 20)->nullable();

            // Contato
            $table->string('email', 120)->unique();
            $table->string('phone', 20)->nullable();

            // Endereço
            $table->string('cep', 10)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('number', 10)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();

            // Autenticação
            $table->string('password');
            $table->rememberToken();

            // Status e auditoria
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes importantes
            $table->index(['tenant_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
