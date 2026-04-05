<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\WhatsAppAutomation;
use App\Models\WhatsAppConnection;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Enviar mensagem de texto via WhatsApp Cloud API (Meta)
     */
    public function sendMessage(int $tenantId, string $phone, string $message, array $meta = []): ?WhatsAppMessage
    {
        $connection = WhatsAppConnection::where('tenant_id', $tenantId)
            ->where('active', true)
            ->first();

        if (!$connection) {
            Log::warning("WhatsApp: Nenhuma conexão ativa para tenant {$tenantId}");
            return null;
        }

        // Salvar mensagem no banco
        $whatsAppMessage = WhatsAppMessage::create([
            'tenant_id'         => $tenantId,
            'appointment_id'    => $meta['appointment_id'] ?? null,
            'campaign_id'       => $meta['campaign_id'] ?? null,
            'recipient_user_id' => $meta['user_id'] ?? null,
            'to_phone'          => $this->formatPhone($phone),
            'to_name'           => $meta['name'] ?? null,
            'type'              => $meta['type'] ?? 'manual',
            'direction'         => 'outbound',
            'content'           => $message,
            'template_name'     => $meta['template_name'] ?? null,
            'template_params'   => $meta['template_params'] ?? null,
            'status'            => 'queued',
        ]);

        // Enviar via API
        try {
            $response = $this->callApi($connection, $phone, $message);

            if ($response && isset($response['messages'][0]['id'])) {
                $whatsAppMessage->update([
                    'status'      => 'sent',
                    'external_id' => $response['messages'][0]['id'],
                    'sent_at'     => now(),
                ]);
            } else {
                $whatsAppMessage->update([
                    'status'        => 'failed',
                    'error_message' => $response['error']['message'] ?? 'Erro desconhecido ao enviar.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp: Erro ao enviar mensagem: {$e->getMessage()}");
            $whatsAppMessage->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $whatsAppMessage;
    }

    /**
     * Chamar API do WhatsApp (Meta Cloud API)
     */
    private function callApi(WhatsAppConnection $connection, string $phone, string $message): ?array
    {
        $url = "https://graph.facebook.com/v21.0/{$connection->phone_number_id}/messages";

        $response = Http::withToken($connection->api_token)
            ->timeout(30)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to'                => $this->formatPhone($phone),
                'type'              => 'text',
                'text'              => ['body' => $message],
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("WhatsApp API Error: " . $response->body());
        return $response->json();
    }

    /**
     * Enviar confirmação de agendamento
     */
    public function sendConfirmation(Appointment $appointment): ?WhatsAppMessage
    {
        $automation = $this->getAutomation($appointment->tenant_id);
        if (!$automation || !$automation->send_confirmation) return null;

        $client = $appointment->client;
        if (!$client || !$client->phone || !$client->whatsapp_optin) return null;

        $message = $this->parseTemplate(
            $automation->confirmation_template ?? $this->defaultConfirmationTemplate(),
            $appointment
        );

        return $this->sendMessage($appointment->tenant_id, $client->phone, $message, [
            'appointment_id' => $appointment->id,
            'user_id'        => $client->id,
            'name'           => $client->name,
            'type'           => 'confirmation',
        ]);
    }

    /**
     * Enviar lembrete de consulta
     */
    public function sendReminder(Appointment $appointment): ?WhatsAppMessage
    {
        $automation = $this->getAutomation($appointment->tenant_id);
        if (!$automation || !$automation->send_reminder) return null;

        $client = $appointment->client;
        if (!$client || !$client->phone || !$client->whatsapp_optin) return null;

        // Verificar se já enviou lembrete
        $alreadySent = WhatsAppMessage::where('appointment_id', $appointment->id)
            ->where('type', 'reminder')
            ->where('status', '!=', 'failed')
            ->exists();

        if ($alreadySent) return null;

        $message = $this->parseTemplate(
            $automation->reminder_template ?? $this->defaultReminderTemplate(),
            $appointment
        );

        return $this->sendMessage($appointment->tenant_id, $client->phone, $message, [
            'appointment_id' => $appointment->id,
            'user_id'        => $client->id,
            'name'           => $client->name,
            'type'           => 'reminder',
        ]);
    }

    /**
     * Enviar aviso de cancelamento
     */
    public function sendCancellation(Appointment $appointment): ?WhatsAppMessage
    {
        $automation = $this->getAutomation($appointment->tenant_id);
        if (!$automation || !$automation->send_cancellation) return null;

        $client = $appointment->client;
        if (!$client || !$client->phone || !$client->whatsapp_optin) return null;

        $message = $this->parseTemplate(
            $automation->cancellation_template ?? $this->defaultCancellationTemplate(),
            $appointment
        );

        return $this->sendMessage($appointment->tenant_id, $client->phone, $message, [
            'appointment_id' => $appointment->id,
            'user_id'        => $client->id,
            'name'           => $client->name,
            'type'           => 'cancellation',
        ]);
    }

    /**
     * Enviar aviso de remarcação
     */
    public function sendReschedule(Appointment $appointment): ?WhatsAppMessage
    {
        $automation = $this->getAutomation($appointment->tenant_id);
        if (!$automation || !$automation->send_reschedule) return null;

        $client = $appointment->client;
        if (!$client || !$client->phone || !$client->whatsapp_optin) return null;

        $message = $this->parseTemplate(
            $automation->reschedule_template ?? $this->defaultRescheduleTemplate(),
            $appointment
        );

        return $this->sendMessage($appointment->tenant_id, $client->phone, $message, [
            'appointment_id' => $appointment->id,
            'user_id'        => $client->id,
            'name'           => $client->name,
            'type'           => 'reschedule',
        ]);
    }

    /**
     * Substituir variáveis dinâmicas no template
     */
    public function parseTemplate(string $template, Appointment $appointment): string
    {
        $appointment->loadMissing(['client', 'professional.user', 'service']);

        $variables = [
            '{{nome}}'         => $appointment->client->name ?? 'Paciente',
            '{{data}}'         => $appointment->start_at->format('d/m/Y'),
            '{{hora}}'         => $appointment->start_at->format('H:i'),
            '{{profissional}}' => $appointment->professional->user->name ?? 'Profissional',
            '{{servico}}'      => $appointment->service->name ?? 'Consulta',
            '{{valor}}'        => 'R$ ' . number_format($appointment->charged_amount ?? $appointment->service->price ?? 0, 2, ',', '.'),
            '{{clinica}}'      => $appointment->tenant->name ?? 'Clínica',
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Processar webhook (status de entrega da Meta)
     */
    public function processWebhook(array $payload): void
    {
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                $value = $change['value'] ?? [];

                // Atualização de status
                if (isset($value['statuses'])) {
                    foreach ($value['statuses'] as $statusUpdate) {
                        $this->updateMessageStatus(
                            $statusUpdate['id'] ?? null,
                            $statusUpdate['status'] ?? null,
                            $statusUpdate['timestamp'] ?? null
                        );
                    }
                }

                // Mensagem recebida (inbound)
                if (isset($value['messages'])) {
                    foreach ($value['messages'] as $inbound) {
                        $this->handleInboundMessage($value, $inbound);
                    }
                }
            }
        }
    }

    /**
     * Atualizar status da mensagem
     */
    private function updateMessageStatus(?string $externalId, ?string $status, ?string $timestamp): void
    {
        if (!$externalId || !$status) return;

        $message = WhatsAppMessage::where('external_id', $externalId)->first();
        if (!$message) return;

        $statusMap = [
            'sent'      => 'sent',
            'delivered' => 'delivered',
            'read'      => 'read',
            'failed'    => 'failed',
        ];

        $newStatus = $statusMap[$status] ?? null;
        if (!$newStatus) return;

        $updates = ['status' => $newStatus];

        if ($newStatus === 'delivered') {
            $updates['delivered_at'] = $timestamp ? Carbon::createFromTimestamp($timestamp) : now();
        }
        if ($newStatus === 'read') {
            $updates['read_at'] = $timestamp ? Carbon::createFromTimestamp($timestamp) : now();
        }

        $message->update($updates);

        // Atualizar contadores da campanha
        if ($message->campaign_id) {
            $this->updateCampaignCounters($message->campaign_id);
        }
    }

    /**
     * Processar mensagem recebida
     */
    private function handleInboundMessage(array $value, array $inbound): void
    {
        $phone = $inbound['from'] ?? null;
        if (!$phone) return;

        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        $connection = WhatsAppConnection::where('phone_number_id', $phoneNumberId)->first();
        if (!$connection) return;

        $text = $inbound['text']['body'] ?? '[mídia]';

        WhatsAppMessage::create([
            'tenant_id'  => $connection->tenant_id,
            'to_phone'   => $phone,
            'to_name'    => $value['contacts'][0]['profile']['name'] ?? null,
            'type'       => 'reply',
            'direction'  => 'inbound',
            'content'    => $text,
            'status'     => 'received',
            'external_id'=> $inbound['id'] ?? null,
        ]);

        // Auto-confirmar se resposta = "1"
        if (trim($text) === '1') {
            $this->autoConfirmAppointment($connection->tenant_id, $phone);
        }
    }

    /**
     * Confirmar agendamento automaticamente via resposta "1"
     */
    private function autoConfirmAppointment(int $tenantId, string $phone): void
    {
        // Buscar último agendamento pendente do paciente
        $appointment = Appointment::where('tenant_id', $tenantId)
            ->whereHas('client', fn($q) => $q->where('phone', 'LIKE', '%' . substr($phone, -8)))
            ->where('status', 'pending')
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->first();

        if ($appointment) {
            $appointment->update(['status' => 'confirmed']);
        }
    }

    /**
     * Atualizar contadores da campanha
     */
    private function updateCampaignCounters(int $campaignId): void
    {
        $campaign = \App\Models\WhatsAppCampaign::find($campaignId);
        if (!$campaign) return;

        $campaign->update([
            'sent_count'      => $campaign->messages()->where('status', '!=', 'queued')->count(),
            'delivered_count' => $campaign->messages()->where('status', 'delivered')->orWhere('status', 'read')->count(),
            'read_count'      => $campaign->messages()->where('status', 'read')->count(),
            'failed_count'    => $campaign->messages()->where('status', 'failed')->count(),
        ]);
    }

    /**
     * Formatar número de telefone para padrão E.164
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        // Se não começa com 55, adicionar código do Brasil
        if (!str_starts_with($phone, '55') && strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }

        return $phone;
    }

    /**
     * Obter automação do tenant
     */
    private function getAutomation(int $tenantId): ?WhatsAppAutomation
    {
        return WhatsAppAutomation::where('tenant_id', $tenantId)
            ->where('active', true)
            ->first();
    }

    // ── Templates Padrão ──

    private function defaultConfirmationTemplate(): string
    {
        return "Olá {{nome}} 👋\n\nSua consulta está confirmada:\n\n📅 Data: {{data}}\n⏰ Hora: {{hora}}\n👨‍⚕️ Profissional: {{profissional}}\n💼 Serviço: {{servico}}\n\nResponda:\n1️⃣ Confirmar\n2️⃣ Remarcar\n3️⃣ Cancelar";
    }

    private function defaultReminderTemplate(): string
    {
        return "Olá {{nome}} 👋\n\nLembrete: você tem uma consulta amanhã!\n\n📅 Data: {{data}}\n⏰ Hora: {{hora}}\n👨‍⚕️ Profissional: {{profissional}}\n\nResponda:\n1️⃣ Confirmar\n2️⃣ Remarcar\n3️⃣ Cancelar";
    }

    private function defaultCancellationTemplate(): string
    {
        return "Olá {{nome}},\n\nSua consulta do dia {{data}} às {{hora}} com {{profissional}} foi cancelada.\n\nPara reagendar, entre em contato conosco.\n\n{{clinica}}";
    }

    private function defaultRescheduleTemplate(): string
    {
        return "Olá {{nome}},\n\nSua consulta foi remarcada:\n\n📅 Nova data: {{data}}\n⏰ Novo horário: {{hora}}\n👨‍⚕️ Profissional: {{profissional}}\n\nResponda:\n1️⃣ Confirmar\n3️⃣ Cancelar";
    }
}
