<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_schedules', function (Blueprint $table) {
            $table->id();

            // 🔹 Chaves de relacionamento
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('professional_id');

            // 🔹 Dia da semana como número (1–7), compatível com controller
            $table->unsignedTinyInteger('day_of_week')->comment('1=Seg, 2=Ter, 3=Qua, 4=Qui, 5=Sex, 6=Sáb, 7=Dom');

            // 🔹 Configuração de horários
            $table->boolean('available')->default(true);
            $table->time('start_hour')->nullable();
            $table->time('end_hour')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->integer('duration_min')->default(30);

            $table->timestamps();

            // 🔹 Relacionamento com profissionais e tenants
            $table->foreign('professional_id')
                ->references('id')
                ->on('professionals')
                ->cascadeOnDelete();

            // 🔹 Índice único por profissional e dia dentro do mesmo tenant
            $table->unique(['tenant_id', 'professional_id', 'day_of_week'], 'unique_prof_tenant_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_schedules');
    }
};
