<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('client_name', 120);
            $table->tinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->boolean('visible')->default(true);
            $table->timestamps();

            // 🔗 Relacionamento com tenants
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
