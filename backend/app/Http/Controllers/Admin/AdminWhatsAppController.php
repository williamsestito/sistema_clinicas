<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignJob;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\User;
use App\Models\WhatsAppAutomation;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Models\WhatsAppConnection;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminWhatsAppController extends Controller
{
    // ════════════════════════════════════════════
    //  Dashboard
    // ════════════════════════════════════════════

    public function dashboard()
    {
        $tenantId = Auth::user()->tenant_id;

        $connection = WhatsAppConnection::where('tenant_id', $tenantId)->first();
        $automation = WhatsAppAutomation::where('tenant_id', $tenantId)->first();

        // Stats últimos 30 dias
        $since = Carbon::now()->subDays(30);

        $stats = WhatsAppMessage::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN direction = 'outbound' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'delivered' OR status = 'read' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as `read`,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN direction = 'inbound' THEN 1 ELSE 0 END) as received
            ")
            ->first();

        // Mensagens por dia (gráfico)
        $byDay = WhatsAppMessage::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->where('direction', 'outbound')
            ->selectRaw("DATE(created_at) as date, COUNT(*) as total, SUM(CASE WHEN status = 'delivered' OR status = 'read' THEN 1 ELSE 0 END) as delivered")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Mensagens por tipo
        $byType = WhatsAppMessage::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->where('direction', 'outbound')
            ->selectRaw("type, COUNT(*) as total")
            ->groupBy('type')
            ->get();

        // Campanhas ativas
        $activeCampaigns = WhatsAppCampaign::where('tenant_id', $tenantId)
            ->whereIn('status', ['scheduled', 'sending'])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // Últimas mensagens
        $recentMessages = WhatsAppMessage::where('tenant_id', $tenantId)
            ->with('recipient')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.whatsapp.dashboard', compact(
            'connection', 'automation', 'stats', 'byDay', 'byType',
            'activeCampaigns', 'recentMessages'
        ));
    }

    // ════════════════════════════════════════════
    //  Mensagens
    // ════════════════════════════════════════════

    public function messages(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = WhatsAppMessage::where('tenant_id', $tenantId)
            ->with(['recipient', 'appointment.client']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to_name', 'like', "%{$search}%")
                  ->orWhere('to_phone', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        return view('admin.whatsapp.messages', compact('messages'));
    }

    public function sendManual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $tenantId = Auth::user()->tenant_id;
        $user = User::where('tenant_id', $tenantId)->findOrFail($request->user_id);

        if (!$user->phone) {
            return back()->with('error', 'Paciente não possui telefone cadastrado.');
        }

        SendWhatsAppMessageJob::dispatch($tenantId, $user->phone, $request->message, [
            'user_id' => $user->id,
            'name'    => $user->name,
            'type'    => 'manual',
        ]);

        return back()->with('success', 'Mensagem adicionada à fila de envio.');
    }

    // ════════════════════════════════════════════
    //  Campanhas
    // ════════════════════════════════════════════

    public function campaigns(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $campaigns = WhatsAppCampaign::where('tenant_id', $tenantId)
            ->withCount('recipients')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.whatsapp.campaigns', compact('campaigns'));
    }

    public function createCampaign()
    {
        return view('admin.whatsapp.campaigns-create');
    }

    public function storeCampaign(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'message_template' => 'required|string|max:4096',
            'segment'          => 'required|in:all,active,inactive,birthday,custom',
            'scheduled_at'     => 'nullable|date|after:now',
            'inactive_days'    => 'nullable|integer|min:30',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Buscar destinatários conforme segmento
        $recipientIds = $this->getSegmentUsers($tenantId, $request->segment, $request->all());

        if ($recipientIds->isEmpty()) {
            return back()->with('error', 'Nenhum paciente encontrado para o segmento selecionado.')->withInput();
        }

        $campaign = WhatsAppCampaign::create([
            'tenant_id'          => $tenantId,
            'created_by_user_id' => Auth::id(),
            'name'               => $request->name,
            'message_template'   => $request->message_template,
            'status'             => $request->scheduled_at ? 'scheduled' : 'draft',
            'segment'            => $request->segment,
            'segment_filters'    => $request->only(['inactive_days']),
            'scheduled_at'       => $request->scheduled_at,
            'total_recipients'   => $recipientIds->count(),
        ]);

        // Criar registros de destinatários
        foreach ($recipientIds as $userId) {
            WhatsAppCampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'user_id'     => $userId,
            ]);
        }

        return redirect()->route('admin.whatsapp.campaigns')->with('success', "Campanha criada com {$recipientIds->count()} destinatários.");
    }

    public function launchCampaign($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $campaign = WhatsAppCampaign::where('tenant_id', $tenantId)->findOrFail($id);

        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Campanha não pode ser iniciada no status atual.');
        }

        // Verificar conexão ativa
        $connection = WhatsAppConnection::where('tenant_id', $tenantId)->where('active', true)->first();
        if (!$connection) {
            return back()->with('error', 'Configure e ative a conexão WhatsApp antes de enviar.');
        }

        SendCampaignJob::dispatch($campaign->id);
        $campaign->update(['status' => 'scheduled']);

        return back()->with('success', 'Campanha adicionada à fila de envio.');
    }

    public function cancelCampaign($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $campaign = WhatsAppCampaign::where('tenant_id', $tenantId)->findOrFail($id);

        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Apenas campanhas em rascunho ou agendadas podem ser canceladas.');
        }

        $campaign->update(['status' => 'cancelled']);

        return back()->with('success', 'Campanha cancelada.');
    }

    public function destroyCampaign($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $campaign = WhatsAppCampaign::where('tenant_id', $tenantId)->findOrFail($id);

        if (!in_array($campaign->status, ['draft', 'cancelled'])) {
            return back()->with('error', 'Apenas campanhas em rascunho ou canceladas podem ser excluídas.');
        }

        $campaign->delete();

        return redirect()->route('admin.whatsapp.campaigns')->with('success', 'Campanha excluída.');
    }

    // ════════════════════════════════════════════
    //  Configurações (Conexão + Automação)
    // ════════════════════════════════════════════

    public function settings()
    {
        $tenantId = Auth::user()->tenant_id;

        $connection = WhatsAppConnection::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['provider' => 'meta', 'status' => 'disconnected']
        );

        $automation = WhatsAppAutomation::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'send_confirmation' => true,
                'send_reminder'     => true,
                'reminder_hours_before' => 24,
                'send_cancellation' => true,
                'send_reschedule'   => true,
            ]
        );

        return view('admin.whatsapp.settings', compact('connection', 'automation'));
    }

    public function updateConnection(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'provider'            => 'required|in:meta,z-api,ultramsg,twilio',
            'phone_number_id'     => 'nullable|string|max:255',
            'business_account_id' => 'nullable|string|max:255',
            'api_token'           => 'nullable|string|max:1000',
            'active'              => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $connection = WhatsAppConnection::where('tenant_id', $tenantId)->firstOrFail();

        $data = $request->only(['provider', 'phone_number_id', 'business_account_id', 'active']);

        // Só atualiza token se enviou um novo
        if ($request->filled('api_token')) {
            $data['api_token'] = $request->api_token;
        }

        $data['active'] = $request->boolean('active');
        $data['status'] = $data['active'] ? 'connected' : 'disconnected';
        if ($data['active']) {
            $data['connected_at'] = now();
        }

        // Gerar webhook verify token se não existir
        if (!$connection->webhook_verify_token) {
            $data['webhook_verify_token'] = bin2hex(random_bytes(16));
        }

        $connection->update($data);

        return back()->with('success', 'Conexão WhatsApp atualizada.');
    }

    public function updateAutomation(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'send_confirmation'     => 'boolean',
            'send_reminder'         => 'boolean',
            'reminder_hours_before' => 'required|integer|min:1|max:72',
            'send_cancellation'     => 'boolean',
            'send_reschedule'       => 'boolean',
            'confirmation_template' => 'nullable|string|max:4096',
            'reminder_template'     => 'nullable|string|max:4096',
            'cancellation_template' => 'nullable|string|max:4096',
            'reschedule_template'   => 'nullable|string|max:4096',
            'active'                => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $automation = WhatsAppAutomation::where('tenant_id', $tenantId)->firstOrFail();

        $automation->update([
            'send_confirmation'     => $request->boolean('send_confirmation'),
            'send_reminder'         => $request->boolean('send_reminder'),
            'reminder_hours_before' => $request->reminder_hours_before,
            'send_cancellation'     => $request->boolean('send_cancellation'),
            'send_reschedule'       => $request->boolean('send_reschedule'),
            'confirmation_template' => $request->confirmation_template,
            'reminder_template'     => $request->reminder_template,
            'cancellation_template' => $request->cancellation_template,
            'reschedule_template'   => $request->reschedule_template,
            'active'                => $request->boolean('active'),
        ]);

        return back()->with('success', 'Automações atualizadas.');
    }

    // ════════════════════════════════════════════
    //  Helpers — Segmentação
    // ════════════════════════════════════════════

    private function getSegmentUsers(int $tenantId, string $segment, array $filters = [])
    {
        $query = User::where('tenant_id', $tenantId)
            ->where('role', 'client')
            ->where('active', true)
            ->where('whatsapp_optin', true)
            ->whereNotNull('phone');

        switch ($segment) {
            case 'active':
                $query->whereHas('clientAppointments', function ($q) {
                    $q->where('start_at', '>=', Carbon::now()->subMonths(3));
                });
                break;

            case 'inactive':
                $days = $filters['inactive_days'] ?? 90;
                $query->where(function ($q) use ($days) {
                    $q->whereDoesntHave('clientAppointments')
                      ->orWhereHas('clientAppointments', function ($sq) use ($days) {
                          $sq->havingRaw('MAX(start_at) < ?', [Carbon::now()->subDays($days)]);
                      });
                });
                break;

            case 'birthday':
                $query->whereMonth('birth_date', Carbon::now()->month)
                      ->whereDay('birth_date', Carbon::now()->day);
                break;

            case 'all':
            default:
                break;
        }

        return $query->pluck('id');
    }
}
