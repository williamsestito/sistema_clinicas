<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('appointment_id');
            $table->enum('channel', ['email', 'whatsapp']);
            $table->enum('type', ['new', 'reminder_24h', 'reminder_2h', 'status_update']);
            $table->string('recipient', 160)->nullable();
            $table->string('template', 80)->nullable();
            $table->dateTime('sent_at')->useCurrent();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->string('error_message', 255)->nullable();
            $table->timestamps();

            // 🔗 Relacionamentos
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
    }
};
