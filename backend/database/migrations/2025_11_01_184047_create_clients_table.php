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

            // identificação básica
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('social_name')->nullable();
            $table->boolean('use_social_name')->default(false);

            // documentos e perfil
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('document')->nullable(); // CPF/CNPJ
            $table->string('rg')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();

            // localização
            $table->string('cep', 12)->nullable();
            $table->string('address')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();

            // preferências e controles
            $table->boolean('consent_marketing')->default(false);
            $table->text('notes')->nullable();
            $table->string('password')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();

            // FKs e índices
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'active']);
            $table->index(['tenant_id', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
