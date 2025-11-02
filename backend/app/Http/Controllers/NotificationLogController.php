<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationLogController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = NotificationLog::with(['appointment.client', 'appointment.professional.user'])
            ->where('tenant_id', $user->tenant_id)
            ->when($request->channel, fn($q) => $q->where('channel', $request->channel))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('sent_at', $request->date))
            ->orderBy('sent_at', 'desc');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'channel' => 'required|in:email,whatsapp',
            'type' => 'required|in:new,reminder_24h,reminder_2h,status_update',
            'recipient' => 'required|string|max:160',
            'template' => 'nullable|string|max:80',
            'status' => 'in:success,failed',
            'error_message' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment = Appointment::find($request->appointment_id);

        if ($appointment->tenant_id !== $authUser->tenant_id) {
            return response()->json(['message' => 'Agendamento pertence a outro tenant.'], 403);
        }

        $log = NotificationLog::create([
            'tenant_id' => $authUser->tenant_id,
            'appointment_id' => $appointment->id,
            'channel' => $request->channel,
            'type' => $request->type,
            'recipient' => $request->recipient,
            'template' => $request->template ?? 'manual_entry',
            'status' => $request->status ?? 'success',
            'error_message' => $request->error_message,
        ]);

        return response()->json([
            'message' => 'Registro de notificação criado com sucesso.',
            'data' => $log->load(['appointment.client', 'appointment.professional.user'])
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();

        $log = NotificationLog::with(['appointment.client', 'appointment.professional.user'])
            ->where('tenant_id', $user->tenant_id)
            ->find($id);

        if (!$log) {
            return response()->json(['message' => 'Log de notificação não encontrado.'], 404);
        }

        return response()->json($log);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $log = NotificationLog::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$log) {
            return response()->json(['message' => 'Registro não encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:success,failed',
            'error_message' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $log->update([
            'status' => $request->status,
            'error_message' => $request->error_message,
            'sent_at' => now(),
        ]);

        return response()->json([
            'message' => 'Status de notificação atualizado com sucesso.',
            'data' => $log
        ]);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        $log = NotificationLog::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$log) {
            return response()->json(['message' => 'Log de notificação não encontrado.'], 404);
        }

        if ($log->status === 'success' && $log->sent_at > now()->subDays(7)) {
            return response()->json(['message' => 'Logs recentes de sucesso não podem ser excluídos.'], 403);
        }

        $log->delete();

        return response()->json(['message' => 'Log de notificação excluído com sucesso.']);
    }
}
