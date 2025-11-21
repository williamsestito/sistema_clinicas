<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Professional;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ClientAppointmentController extends Controller
{
    /**
     * Criar um pré-agendamento (cliente logado)
     */
    public function store(Request $request)
    {
        $client = auth('client')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Você precisa estar logado para agendar.'
            ], 401);
        }

        // Validação
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'procedure'       => 'required|string|max:255',
            'date'            => 'required|date|after_or_equal:today',
            'time'            => 'required|date_format:H:i',
        ]);

        // Monta data+hora
        $startAt = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $endAt   = (clone $startAt)->addMinutes(30);

        // Conflito de horário
        $exists = Appointment::where('professional_id', $validated['professional_id'])
            ->where('start_at', $startAt)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Horário já reservado. Escolha outro.',
            ], 409);
        }

        // Cria o agendamento
        $appointment = Appointment::create([
            'tenant_id'        => $client->tenant_id,
            'client_id'        => $client->id,
            'professional_id'  => $validated['professional_id'],
            'service_id'       => null,
            'start_at'         => $startAt,
            'end_at'           => $endAt,
            'status'           => 'pending',
            'source'           => 'web',
            'notes'            => "Procedimento: {$validated['procedure']}",
        ]);

        // E-mails
        $prof = Professional::with('user')->find($validated['professional_id']);
        $formattedDate = $startAt->format('d/m/Y');
        $formattedTime = $startAt->format('H:i');

        // E-mail cliente
        Mail::raw(
            "Olá {$client->name},\n\n".
            "Seu pré-agendamento foi registrado.\n".
            "📅 Data: {$formattedDate}\n".
            "⏰ Horário: {$formattedTime}\n".
            "Profissional: {$prof->display_name}\n\n".
            "Aguarde a confirmação.",
            fn($msg) => $msg->to($client->email)->subject('Pré-agendamento realizado')
        );

        // E-mail profissional
        if ($prof?->user?->email) {
            Mail::raw(
                "Novo pré-agendamento:\n\n".
                "Cliente: {$client->name}\n".
                "E-mail: {$client->email}\n".
                "Data: {$formattedDate}\n".
                "Horário: {$formattedTime}\n\n".
                "Acesse o painel.",
                fn($msg) => $msg->to($prof->user->email)->subject('Novo pré-agendamento')
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pré-agendamento realizado com sucesso!',
            'appointment' => $appointment
        ]);
    }


    /**
     * JSON consumido pela tela client/appointments
     */
    public function indexJson()
    {
        $client = auth('client')->user();

        $appointments = Appointment::with(['professional.user'])
            ->where('client_id', $client->id)
            ->orderBy('start_at', 'asc')
            ->get();

        $ativos = [];
        $historico = [];

        foreach ($appointments as $a) {

            $item = [
                'id'            => $a->id,
                'professional'  => $a->professional?->display_name ?? 'Indefinido',
                'especialidade' => $a->professional?->specialty
                    ? implode(', ', (array) $a->professional->specialty)
                    : '-',
                'data'          => $a->start_at,
                'hora'          => Carbon::parse($a->start_at)->format('H:i'),
                'status'        => $a->status,
                'endereco'      => $a->professional?->full_address ?? '-',
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
     * Cancelar agendamento
     */
    public function cancel($id)
    {
        $client = auth('client')->user();

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
