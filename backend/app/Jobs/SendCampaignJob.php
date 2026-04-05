<?php

namespace App\Jobs;

use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 3600; // 1h max

    public function __construct(public int $campaignId)
    {
        $this->onQueue('whatsapp');
    }

    public function handle(WhatsAppService $service): void
    {
        $campaign = WhatsAppCampaign::with('recipients.user')->find($this->campaignId);

        if (!$campaign || $campaign->status === 'cancelled') {
            return;
        }

        $campaign->update([
            'status'     => 'sending',
            'started_at' => now(),
        ]);

        $recipients = $campaign->recipients()->where('status', 'pending')->with('user')->get();

        foreach ($recipients as $recipient) {
            $user = $recipient->user;

            if (!$user || !$user->phone || !$user->whatsapp_optin) {
                $recipient->update(['status' => 'failed']);
                continue;
            }

            // Substituir variáveis no template da campanha
            $message = $this->parseCampaignTemplate($campaign->message_template, $user);

            $whatsAppMsg = $service->sendMessage($campaign->tenant_id, $user->phone, $message, [
                'campaign_id' => $campaign->id,
                'user_id'     => $user->id,
                'name'        => $user->name,
                'type'        => 'campaign',
            ]);

            if ($whatsAppMsg) {
                $recipient->update([
                    'message_id' => $whatsAppMsg->id,
                    'status'     => $whatsAppMsg->status === 'failed' ? 'failed' : 'sent',
                ]);
            }

            // Rate limiting: 1 msg a cada 2 segundos para evitar bloqueio
            usleep(2000000);
        }

        // Recarregar contadores
        $campaign->refresh();
        $sent = $campaign->recipients()->where('status', '!=', 'pending')->count();
        $failed = $campaign->recipients()->where('status', 'failed')->count();

        $campaign->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'sent_count'   => $sent - $failed,
            'failed_count' => $failed,
        ]);

        Log::info("WhatsApp Campaign #{$campaign->id} completed. Sent: " . ($sent - $failed) . ", Failed: {$failed}");
    }

    private function parseCampaignTemplate(string $template, $user): string
    {
        $variables = [
            '{{nome}}'  => $user->name ?? 'Paciente',
            '{{email}}' => $user->email ?? '',
            '{{phone}}' => $user->phone ?? '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }
}
