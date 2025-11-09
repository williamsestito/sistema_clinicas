<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('specialty', 120)->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->boolean('show_prices')->default(true);
            $table->time('default_start_hour')->nullable();       // início padrão
            $table->time('default_end_hour')->nullable();         // fim padrão
            $table->integer('default_consultation_time')->default(30); // minutos
            $table->boolean('active')->default(true);
            $table->timestamps();

            // 🔗 Relacionamentos
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->cascadeOnDelete();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
