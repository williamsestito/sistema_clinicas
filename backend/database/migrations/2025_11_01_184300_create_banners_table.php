<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('title', 120)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->string('image_url', 255)->nullable();
            $table->string('link_url', 255)->nullable();
            $table->integer('position')->default(0);
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('banners');
    }
};
