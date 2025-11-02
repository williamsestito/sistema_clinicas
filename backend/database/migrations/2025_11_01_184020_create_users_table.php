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

            // FK será adicionada em migration separada (para evitar dependência circular)
            $table->unsignedBigInteger('tenant_id')->nullable();

            $table->enum('role', ['owner', 'admin', 'professional', 'frontdesk', 'client'])->default('client');
            $table->string('name', 120);
            $table->string('email', 120)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
