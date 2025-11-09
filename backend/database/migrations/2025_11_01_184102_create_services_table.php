<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('professional_id');
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->integer('duration_min')->default(30);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('visible_to_clients')->default(true);
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('services');
    }
};
