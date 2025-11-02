<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('professional_id');
            $table->unsignedBigInteger('service_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->enum('status', ['pending', 'confirmed', 'done', 'cancelled', 'no_show'])->default('pending');
            $table->enum('source', ['web', 'staff', 'whatsapp'])->default('web');
            $table->text('notes')->nullable();
            $table->timestamps();

            // 🔗 Relacionamentos
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();

            $table->foreign('professional_id')
                ->references('id')
                ->on('professionals')
                ->cascadeOnDelete();

            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->cascadeOnDelete();

            // 📈 Índice para otimizar buscas por horário e profissional
            $table->index(['professional_id', 'start_at'], 'idx_appointment_prof_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
