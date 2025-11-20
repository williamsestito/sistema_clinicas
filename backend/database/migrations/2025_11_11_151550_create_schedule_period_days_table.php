<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_period_days', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('professional_id');
            $table->unsignedBigInteger('period_id');

            // Dia da semana (0 = Domingo, 6 = Sábado)
            $table->unsignedTinyInteger('weekday');

            // Horários de atendimento
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Intervalo
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            // Duração da consulta
            $table->unsignedSmallInteger('duration')->default(30);

            // Disponibilidade do dia
            $table->boolean('available')->default(true);

            $table->timestamps();

            /*
             * Restrição correta para evitar duplicidade
             * Um mesmo período não pode ter dois registros para o mesmo weekday
             */
            $table->unique(['period_id', 'weekday']);

            // Foreign Keys
            $table->foreign('tenant_id')
                ->references('id')->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('professional_id')
                ->references('id')->on('professionals')
                ->cascadeOnDelete();

            $table->foreign('period_id')
                ->references('id')->on('schedule_periods')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_period_days');
    }
};
