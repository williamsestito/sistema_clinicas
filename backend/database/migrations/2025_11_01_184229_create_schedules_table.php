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
            $table->tinyInteger('weekday')->comment('0 = Domingo, 6 = Sábado');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_min')->default(30);
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
                  ->on('professionals')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
