<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('professional_id');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedSmallInteger('duration_min')->default(30);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('professional_id')->references('id')->on('professionals')->cascadeOnDelete();

            $table->unique(['tenant_id','professional_id','name'], 'uniq_prof_proc_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_procedures');
    }
};
