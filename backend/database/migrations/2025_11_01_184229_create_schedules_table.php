<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('professional_id');
            
            // Dias da semana (1 = Segunda, 7 = Domingo)
            $table->tinyInteger('weekday')->comment('1 = Segunda, 7 = Domingo');

            // Controle de disponibilidade
            $table->boolean('active')->default(false);

            // Horários de trabalho
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Intervalo de almoço/pausa
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            // Duração dos atendimentos (em minutos)
            $table->integer('duration_min')->default(30);

            // Buffers opcionais antes/depois dos atendimentos
            $table->integer('buffer_before')->default(0);
            $table->integer('buffer_after')->default(0);

            $table->timestamps();

            // 🔗 Relacionamentos
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->cascadeOnDelete();

            $table->foreign('professional_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
