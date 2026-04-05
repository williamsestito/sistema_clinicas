<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConnection;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verificação do webhook (Meta exige GET para validar)
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token) {
            $connection = WhatsAppConnection::where('webhook_verify_token', $token)->first();

            if ($connection) {
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }
        }

        return response('Forbidden', 403);
    }

    /**
     * Receber eventos do WhatsApp (status, mensagens recebidas)
     */
    public function handle(Request $request, WhatsAppService $service)
    {
        $payload = $request->all();

        $service->processWebhook($payload);

        return response()->json(['status' => 'ok'], 200);
    }
}
