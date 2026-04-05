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
            $table->unsignedBigInteger('client_id');        // usuário com role=client
            $table->unsignedBigInteger('professional_id');  // usuário com role=professional

            // Agora pode ser NULL
            $table->unsignedBigInteger('service_id')->nullable();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->enum('status', [
                'pending',
                'confirmed',
                'done',
                'cancelled',
                'no_show'
            ])->default('pending');

            /**
             * 🔥 Agora aceitando origem "api"
             */
            $table->enum('source', [
                'web',
                'staff',
                'whatsapp',
                'api'
            ])->default('api');

            /**
             * Detalhes adicionais
             */
            $table->text('notes')->nullable();              // Observações do agendamento
            $table->string('cancel_reason')->nullable();    // Motivo do cancelamento
            $table->string('reschedule_reason')->nullable();// Motivo do reagendamento (profissional ou cliente)

            $table->timestamps();

            /**
             * Foreign Keys
             */
            $table->foreign('tenant_id')
                ->references('id')->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('client_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('professional_id')
                ->references('id')->on('professionals')
                ->cascadeOnDelete();

            // FK aceita NULL → se o serviço for apagado, não quebra o histórico
            $table->foreign('service_id')
                ->references('id')->on('services')
                ->nullOnDelete();

            /**
             * Índices otimizados para agenda
             */
            $table->index(['professional_id', 'start_at'], 'idx_professional_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
