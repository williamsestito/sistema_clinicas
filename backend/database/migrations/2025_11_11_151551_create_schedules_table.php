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

            // 🔗 Multi-tenant
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('professional_id');

            // 🔗 Cada horário pertence a UM período
            $table->unsignedBigInteger('period_id');

            // 🔹 Dia da semana (0 = Domingo ... 6 = Sábado)
            $table->tinyInteger('weekday')->comment('0 = Domingo, 6 = Sábado');

            // ⏰ Horário de atendimento
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // ☕ Intervalo / pausa
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            // 🕒 Tempo mínimo da consulta
            $table->unsignedSmallInteger('slot_min')->default(30);

            // 🔄 Marcadores de status (edição / salvo)
            $table->boolean('active')->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEYS
            |--------------------------------------------------------------------------
            | Importante: estas tabelas precisam existir ANTES desta migration.
            | Você já organizou isso e agora está na ordem certa.
            */

            $table->foreign('tenant_id')
                ->references('id')->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('professional_id')
                ->references('id')->on('professionals')
                ->cascadeOnDelete();

            $table->foreign('period_id')
                ->references('id')->on('schedule_periods')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | CONSTRAINT ÚNICA
            |--------------------------------------------------------------------------
            | Evita duplicidade de horários por:
            | - tenant
            | - profissional
            | - período
            | - dia da semana
            */
            $table->unique(
                ['tenant_id','professional_id','period_id','weekday'],
                'unique_schedule_period_day'
            );

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES EXTRA PARA PERFORMANCE
            |--------------------------------------------------------------------------
            */
            $table->index(['tenant_id','professional_id']);
            $table->index(['period_id']);
            $table->index(['weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
