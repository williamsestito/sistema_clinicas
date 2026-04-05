<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conexões WhatsApp por tenant
        Schema::create('whatsapp_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('meta'); // meta, z-api, ultramsg, twilio
            $table->string('phone_number_id')->nullable();
            $table->string('business_account_id')->nullable();
            $table->text('api_token')->nullable();
            $table->string('webhook_verify_token', 100)->nullable();
            $table->boolean('active')->default(false);
            $table->enum('status', ['disconnected', 'connected', 'error'])->default('disconnected');
            $table->timestamp('connected_at')->nullable();
            $table->json('settings')->nullable(); // limites, horários de envio, etc
            $table->timestamps();

            $table->unique('tenant_id'); // 1 conexão por tenant
        });

        // Mensagens WhatsApp
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('to_phone', 20);
            $table->string('to_name')->nullable();
            $table->enum('type', ['confirmation', 'reminder', 'cancellation', 'reschedule', 'campaign', 'manual', 'reply'])->default('manual');
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            $table->text('content');
            $table->string('template_name')->nullable();
            $table->json('template_params')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed', 'received'])->default('queued');
            $table->string('external_id')->nullable(); // ID da API externa
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index('external_id');
        });

        // Campanhas WhatsApp
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('message_template');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'cancelled'])->default('draft');
            $table->enum('segment', ['all', 'active', 'inactive', 'birthday', 'custom'])->default('all');
            $table->json('segment_filters')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('read_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // Destinatários de campanhas
        Schema::create('whatsapp_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('whatsapp_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('whatsapp_messages')->nullOnDelete();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id']);
        });

        // Opt-in WhatsApp na tabela users (LGPD)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('whatsapp_optin')->default(false)->after('active');
            $table->timestamp('whatsapp_optin_at')->nullable()->after('whatsapp_optin');
        });

        // Configurações de automação por tenant
        Schema::create('whatsapp_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->boolean('send_confirmation')->default(true);
            $table->boolean('send_reminder')->default(true);
            $table->unsignedSmallInteger('reminder_hours_before')->default(24);
            $table->boolean('send_cancellation')->default(true);
            $table->boolean('send_reschedule')->default(true);
            $table->text('confirmation_template')->nullable();
            $table->text('reminder_template')->nullable();
            $table->text('cancellation_template')->nullable();
            $table->text('reschedule_template')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaign_recipients');
        Schema::dropIfExists('whatsapp_campaigns');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_automations');
        Schema::dropIfExists('whatsapp_connections');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_optin', 'whatsapp_optin_at']);
        });
    }
};
