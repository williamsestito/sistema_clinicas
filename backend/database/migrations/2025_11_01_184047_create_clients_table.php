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

            // Tenant (multi-clínicas)
            $table->unsignedBigInteger('tenant_id');

            /*
            |--------------------------------------------------------------------------
            | RELAÇÃO OPCIONAL COM USER (caso o profissional cadastre)
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('user_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | IDENTIFICAÇÃO DO CLIENTE
            |--------------------------------------------------------------------------
            */
            $table->string('name');
            $table->string('social_name')->nullable();
            $table->boolean('use_social_name')->default(false);

            /*
            |--------------------------------------------------------------------------
            | DOCUMENTOS / PERFIL
            |--------------------------------------------------------------------------
            */
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('document', 18)->nullable(); // CPF/CNPJ
            $table->string('rg')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('civil_status', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | ENDEREÇO
            |--------------------------------------------------------------------------
            */
            $table->string('cep', 12)->nullable();
            $table->string('address')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | PREFERÊNCIAS E CONTROLES
            |--------------------------------------------------------------------------
            */
            $table->boolean('consent_marketing')->default(false);
            $table->text('notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | AUTENTICAÇÃO
            |--------------------------------------------------------------------------
            */
            $table->string('password')->nullable();     // login do cliente
            $table->boolean('active')->default(true);   // bloqueio / desativação

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEYS
            |--------------------------------------------------------------------------
            */
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->cascadeOnDelete();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES OTIMIZADOS
            |--------------------------------------------------------------------------
            */
            $table->index(['tenant_id', 'active']);
            $table->index(['tenant_id', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
