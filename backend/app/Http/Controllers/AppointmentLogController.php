<?php

namespace App\Http\Controllers;

use App\Models\AppointmentLog;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppointmentLogController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = AppointmentLog::with(['appointment.client', 'appointment.professional.user'])
            ->whereHas('appointment', fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->when($request->appointment_id, fn($q) => $q->where('appointment_id', $request->appointment_id))
            ->when($request->user_id, fn($q) => $q->where('changed_by_user_id', $request->user_id))
            ->orderBy('changed_at', 'desc');

        return response()->json($query->paginate(20));
    }


    public function store(Request $request)
    {
        $authUser = Auth::user();

        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'note' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment = Appointment::find($request->appointment_id);

        if ($appointment->tenant_id !== $authUser->tenant_id) {
            return response()->json(['message' => 'Agendamento pertence a outro tenant.'], 403);
        }

        $log = AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'changed_by_user_id' => $authUser->id,
            'from_status' => $appointment->status,
            'to_status' => $appointment->status,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Log registrado com sucesso.',
            'data' => $log->load(['appointment.client', 'appointment.professional.user'])
        ], 201);
    }


    public function show($id)
    {
        $user = Auth::user();

        $log = AppointmentLog::with(['appointment.client', 'appointment.professional.user', 'changedBy'])
            ->whereHas('appointment', fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->find($id);

        if (!$log) {
            return response()->json(['message' => 'Log não encontrado.'], 404);
        }

        return response()->json($log);
    }


    public function destroy($id)
    {
        $authUser = Auth::user();

        $log = AppointmentLog::with('appointment')
            ->whereHas('appointment', fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->find($id);

        if (!$log) {
            return response()->json(['message' => 'Log não encontrado.'], 404);
        }

        
        if (str_contains(strtolower($log->note), 'criado') || str_contains(strtolower($log->note), 'status alterado')) {
            return response()->json(['message' => 'Logs automáticos não podem ser excluídos.'], 403);
        }

        $log->delete();

        return response()->json(['message' => 'Log excluído com sucesso.']);
    }
}
