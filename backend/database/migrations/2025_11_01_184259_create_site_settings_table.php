<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('site_title', 120)->nullable();
            $table->string('tagline', 255)->nullable();
            $table->string('about_title', 120)->nullable();
            $table->text('about_text')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email', 120)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('instagram_url', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('whatsapp_url', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // 🔗 Relacionamento com tenants
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
