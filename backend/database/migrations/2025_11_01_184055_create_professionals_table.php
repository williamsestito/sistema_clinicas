<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();

            // Relacionamentos obrigatórios
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');

            //------------------------------------------------------------------
            // Identidade profissional
            //------------------------------------------------------------------
            $table->json('specialty')->nullable();               // múltiplas especialidades
            $table->string('registration_type', 20)->nullable(); // CRM, CRO, CRP, etc.
            $table->string('registration_number', 50)->nullable();

            //------------------------------------------------------------------
            // Perfil profissional
            //------------------------------------------------------------------
            $table->string('photo_url', 255)->nullable();
            $table->text('bio')->nullable();        // descrição curta
            $table->text('about')->nullable();      // descrição longa
            $table->integer('experience_years')->nullable();

            //------------------------------------------------------------------
            // Formação e certificações
            //------------------------------------------------------------------
            $table->text('education')->nullable();          // formação acadêmica
            $table->json('specializations')->nullable();    // cursos, pós, certificações

            //------------------------------------------------------------------
            // Endereço profissional
            //------------------------------------------------------------------
            $table->string('state', 2)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('address')->nullable();          // logradouro
            $table->string('number', 20)->nullable();
            $table->string('district', 120)->nullable();    // bairro
            $table->string('complement')->nullable();
            $table->string('zipcode', 12)->nullable();

            //------------------------------------------------------------------
            // Contatos públicos
            //------------------------------------------------------------------
            $table->string('phone', 30)->nullable();
            $table->string('email_public')->nullable();     // NÃO usar email do user

            //------------------------------------------------------------------
            // Redes sociais
            //------------------------------------------------------------------
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('website_url')->nullable();

            //------------------------------------------------------------------
            // Configurações de agenda
            //------------------------------------------------------------------
            $table->time('default_start_hour')->nullable();
            $table->time('default_end_hour')->nullable();
            $table->integer('default_consultation_time')->default(30);

            //------------------------------------------------------------------
            // Flags
            //------------------------------------------------------------------
            $table->boolean('show_prices')->default(true);
            $table->boolean('active')->default(true);

            $table->timestamps();

            //------------------------------------------------------------------
            // Chaves estrangeiras
            //------------------------------------------------------------------
            $table->foreign('tenant_id')
                ->references('id')->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
