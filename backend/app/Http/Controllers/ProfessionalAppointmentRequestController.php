<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use Carbon\Carbon;

class ProfessionalAppointmentRequestController extends Controller
{
    /**
     * Lista de solicitações pendentes para o profissional logado
     */
    public function index()
    {
        $user = Auth::user();
        $professional = $user->professional;

        $requests = Appointment::with(['client', 'service'])
            ->where('professional_id', $professional->id)
            ->where('status', 'pending')
            ->orderBy('start_at')
            ->get();

        return view('professional.appointments.requests', compact('requests'));
    }

    public function approve(Request $request, $id)
    {
        $appt = Appointment::with('client')->findOrFail($id);

        if ($appt->status !== 'pending') {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Este agendamento não está mais pendente.'], 400)
                : back()->with('error', 'Este agendamento não está mais pendente.');
        }

        try {
            $appt->update(['status' => 'confirmed']);

            // Email opcional
            if ($appt->client?->email) {
                Mail::raw(
                    "Agendamento confirmado!\nData: {$appt->start_at->format('d/m/Y H:i')}",
                    fn($msg) => $msg->to($appt->client->email)->subject('Agendamento Confirmado')
                );
            }

            AppointmentLog::create([
                'appointment_id' => $appt->id,
                'user_id' => Auth::id(),
                'action' => 'approve',
                'description' => 'Agendamento aprovado pelo profissional'
            ]);

            // 🔥 resposta JSON para AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Agendamento aprovado com sucesso!'
                ]);
            }

            // Caso não seja AJAX (acesso tradicional)
            return back()->with('success', 'Agendamento aprovado com sucesso!');

        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Erro ao aprovar agendamento.', 'debug' => $e->getMessage()], 500)
                : back()->with('error', 'Erro ao aprovar agendamento.');
        }
    }


    /**
     * Cancelamento pelo profissional
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $appt = Appointment::with('client')->findOrFail($id);

        if ($appt->status === 'cancelled') {
            return back()->with('error', 'Este agendamento já está cancelado.');
        }

        $appt->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->reason,
        ]);

        // E-mail ao cliente
        if ($appt->client?->email) {
            Mail::raw(
                "Seu agendamento foi cancelado.\n\n".
                "Motivo: ".($request->reason ?: 'Não informado'),
                fn($msg) => $msg->to($appt->client->email)->subject('Agendamento Cancelado')
            );
        }

        // Log
        AppointmentLog::create([
            'appointment_id' => $appt->id,
            'user_id' => Auth::id(),
            'action' => 'reject',
            'description' => 'Cancelado pelo profissional'
        ]);

        return back()->with('success', 'Agendamento rejeitado.');
    }


    /**
     * 🔥 Reagendamento via AJAX
     * Retorna **exclusivamente JSON**
     */
    public function reschedule(Request $request, $id)
    {
        try {
            $request->validate([
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i'
            ]);

            $appt = Appointment::with('client')->findOrFail($id);
            $user = Auth::user();

            $startAt = Carbon::parse("{$request->date} {$request->time}");
            $endAt   = $startAt->copy()->addMinutes(30);

            // Conflito de agenda
            $conflict = Appointment::where('professional_id', $appt->professional_id)
                ->where('id', '!=', $appt->id)
                ->where('start_at', $startAt)
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe um agendamento neste horário.'
                ], 422);
            }

            $oldStart = $appt->start_at->copy();

            // Atualização
            $appt->update([
                'start_at' => $startAt,
                'end_at'   => $endAt,
                'status'   => 'confirmed',
                'notes'    => trim(($appt->notes ?? '') . ' (Reagendado pelo profissional)')
            ]);

            // Log
            AppointmentLog::create([
                'appointment_id' => $appt->id,
                'user_id'        => $user->id,
                'action'         => 'reschedule',
                'description'    => "Reagendado de {$oldStart->format('d/m/Y H:i')} para {$startAt->format('d/m/Y H:i')}",
            ]);

            // E-mail ao cliente
            if ($appt->client?->email) {
                Mail::raw(
                    "Sua consulta foi reagendada!\n\n".
                    "Nova data/horário: {$startAt->format('d/m/Y H:i')}",
                    fn($msg) => $msg->to($appt->client->email)->subject('Consulta Reagendada')
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Consulta reagendada com sucesso!'
            ]);

        } catch (\Throwable $e) {
            // Evita retornar HTML
            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado ao reagendar.',
                'error'   => $e->getMessage() // facilita debug
            ], 500);
        }
    }
}
