<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public int $tenantId,
        public string $phone,
        public string $message,
        public array $meta = []
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(WhatsAppService $service): void
    {
        $service->sendMessage($this->tenantId, $this->phone, $this->message, $this->meta);
    }
}
