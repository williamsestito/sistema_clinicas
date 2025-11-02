<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->dateTime('changed_at')->useCurrent();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            // 🔗 Relacionamentos
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->cascadeOnDelete();

            $table->foreign('changed_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_logs');
    }
};
