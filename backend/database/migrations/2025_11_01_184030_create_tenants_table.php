<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('cnpj', 18)->nullable();
            $table->string('im', 30)->nullable();

            // 🔹 Dados de contato básicos
            $table->string('email', 120)->nullable();
            $table->string('phone', 20)->nullable();

            // 🔹 Proprietário (owner)
            $table->unsignedBigInteger('owner_user_id')->nullable();

            // 🔹 Branding e configurações
            $table->string('logo_url')->nullable();
            $table->string('primary_color', 10)->default('#004d40');
            $table->string('secondary_color', 10)->default('#009688');
            $table->boolean('active')->default(true);
            $table->json('settings')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

