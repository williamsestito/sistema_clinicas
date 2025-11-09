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
            $table->unsignedBigInteger('service_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->enum('status', ['pending', 'confirmed', 'done', 'cancelled', 'no_show'])->default('pending');
            $table->enum('source', ['web', 'staff', 'whatsapp'])->default('web');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('professional_id')->references('id')->on('professionals')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();

            $table->index(['professional_id', 'start_at'], 'idx_professional_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
