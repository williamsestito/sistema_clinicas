<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\WhatsAppAutomation;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('whatsapp');
    }

    public function handle(WhatsAppService $service): void
    {
        // Buscar todas as automações ativas
        $automations = WhatsAppAutomation::where('active', true)
            ->where('send_reminder', true)
            ->get();

        foreach ($automations as $automation) {
            $hoursBefore = $automation->reminder_hours_before ?? 24;

            // Buscar agendamentos que iniciam daqui a X horas (janela de 1h)
            $from = Carbon::now()->addHours($hoursBefore);
            $to = $from->copy()->addHour();

            $appointments = Appointment::where('tenant_id', $automation->tenant_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereBetween('start_at', [$from, $to])
                ->with(['client', 'professional.user', 'service', 'tenant'])
                ->get();

            foreach ($appointments as $appointment) {
                try {
                    $service->sendReminder($appointment);
                } catch (\Exception $e) {
                    Log::error("WhatsApp Reminder Error (Apt #{$appointment->id}): {$e->getMessage()}");
                }
            }

            if ($appointments->count() > 0) {
                Log::info("WhatsApp: {$appointments->count()} lembretes enviados para tenant #{$automation->tenant_id}");
            }
        }
    }
}
