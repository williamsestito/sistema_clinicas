<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_periods', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('professional_id');

            $table->date('start_date');
            $table->date('end_date');

            $table->json('active_days'); // ex: [1,2,3]

            $table->time('start_time')->default('08:00');
            $table->time('end_time')->default('18:00');

            $table->unsignedSmallInteger('duration')->default(30);

            $table->boolean('available')->default(true);

            $table->timestamps();

            // FKs
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('professional_id')->references('id')->on('professionals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_periods');
    }
};
