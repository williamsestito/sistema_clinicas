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

            // Dia da semana (0=Dom, 6=Sáb)
            $table->unsignedTinyInteger('weekday');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            $table->unsignedSmallInteger('duration')->default(30);

            $table->boolean('available')->default(true);

            $table->timestamps();

            // Evita duplicidade por profissional
            $table->unique(['tenant_id', 'professional_id', 'weekday']);

            // FKs
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('professional_id')
                ->references('id')
                ->on('professionals')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_period_days');
    }
};
