<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Professional;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ClientAppointmentController extends Controller
{
    /**
     * Retorna o cliente autenticado via API OU WEB.
     */
    protected function authenticatedClient()
    {
        return Auth::guard('client')->user()
            ?? Auth::guard('client_api')->user();
    }


    /**
     * Criar pré-agendamento (WEB + API)
     */
    public function store(Request $request)
    {
        $client = $this->authenticatedClient();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado.'
            ], 401);
        }

        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'procedure'       => 'required|string|max:255',
            'date'            => 'required|date|after_or_equal:today',
            'time'            => 'required|date_format:H:i',
        ]);

        // Monta o datetime correto
        $startAt = Carbon::parse("{$validated['date']} {$validated['time']}:00");
        $endAt   = $startAt->copy()->addMinutes(30);

        // Verifica conflito
        $existing = Appointment::where('professional_id', $validated['professional_id'])
            ->where('start_at', $startAt)
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Este horário já está reservado.'
            ], 409);
        }

        // Cria agendamento
        $appointment = Appointment::create([
            'tenant_id'        => $client->tenant_id,
            'client_id'        => $client->id,
            'professional_id'  => $validated['professional_id'],
            'service_id'       => null,
            'start_at'         => $startAt,
            'end_at'           => $endAt,
            'status'           => 'pending',
            'source'           => Auth::guard('client_api')->check() ? 'api' : 'web',
            'notes'            => "Procedimento: {$validated['procedure']}",
        ]);

        // Profissional
        $prof = Professional::with('user')->find($validated['professional_id']);

        $dateFormatted = $startAt->format('d/m/Y');
        $timeFormatted = $startAt->format('H:i');

        /**
         * Email cliente
         */
        Mail::raw(
            "Olá {$client->name},\n\n" .
            "Seu pré-agendamento foi registrado.\n" .
            "📅 Data: {$dateFormatted}\n" .
            "⏰ Horário: {$timeFormatted}\n" .
            "👨‍⚕️ Profissional: {$prof->display_name}\n\n" .
            "Aguarde a confirmação.",
            fn($msg) => $msg->to($client->email)->subject('Pré-agendamento realizado')
        );

        /**
         * Email profissional
         */
        if ($prof?->user?->email) {
            Mail::raw(
                "Novo pré-agendamento:\n\n" .
                "Cliente: {$client->name}\n" .
                "E-mail: {$client->email}\n" .
                "Data: {$dateFormatted}\n" .
                "Horário: {$timeFormatted}\n\n",
                fn($msg) => $msg->to($prof->user->email)->subject('Novo pré-agendamento')
            );
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Pré-agendamento enviado com sucesso!',
            'appointment' => $appointment
        ], 201);
    }


    /**
     * Lista os agendamentos do cliente — JSON
     */
    public function indexJson()
    {
        $client = $this->authenticatedClient();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado.'
            ], 401);
        }

        $appointments = Appointment::with(['professional.user'])
            ->where('client_id', $client->id)
            ->orderBy('start_at', 'asc')
            ->get();

        $ativos = [];
        $historico = [];

        foreach ($appointments as $a) {

            // Corrige especialidades (array / json / string)
            $especialidade = '-';
            if ($a->professional?->specialty) {
                $esp = is_array($a->professional->specialty)
                    ? $a->professional->specialty
                    : json_decode($a->professional->specialty, true);

                if (is_array($esp)) {
                    $especialidade = implode(', ', $esp);
                }
            }

            // Monta retorno
            $item = [
                'id'            => $a->id,
                'professional'  => $a->professional?->display_name ?? 'Indefinido',
                'service'       => $a->service_text ?? ($a->notes ?? 'Consulta'),
                'especialidade' => $especialidade,
                'start_at'      => $a->start_at ? $a->start_at->format('Y-m-d H:i:s') : null,
                'data'          => $a->start_at ? $a->start_at->format('Y-m-d') : null,
                'hora'          => $a->start_at ? $a->start_at->format('H:i') : null,
                'status'        => $a->status,
                'status_text'   => $a->status_label ?? ucfirst($a->status),
                'endereco'      => $a->professional?->full_address ?? '-',
                'notes'         => $a->notes,
            ];

            if (in_array($a->status, ['pending', 'confirmed'])) {
                $ativos[] = $item;
            } else {
                $historico[] = $item;
            }
        }

        return response()->json([
            'success'   => true,
            'ativos'    => $ativos,
            'historico' => $historico
        ]);
    }


    /**
     * Cancelar agendamento (WEB + API)
     */
    public function cancel($id)
    {
        $client = $this->authenticatedClient();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado.'
            ], 401);
        }

        $appointment = Appointment::where('client_id', $client->id)
            ->where('id', $id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Agendamento não encontrado.'
            ], 404);
        }

        if ($appointment->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Este agendamento já está cancelado.'
            ]);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Agendamento cancelado com sucesso.'
        ]);
    }
}

