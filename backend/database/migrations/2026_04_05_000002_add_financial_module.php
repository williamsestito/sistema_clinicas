<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // B) Valor cobrado no agendamento (permite ajuste individual)
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('charged_amount', 10, 2)->nullable()->after('notes');
        });

        // C) Módulo financeiro avulso
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 80);
            $table->enum('type', ['income', 'expense']);
            $table->string('color', 10)->default('#6B7280');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->enum('type', ['income', 'expense']);
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'pix', 'transfer', 'plan', 'other'])->default('cash');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('financial_categories')->nullOnDelete();
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
        Schema::dropIfExists('financial_categories');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('charged_amount');
        });
    }
};
