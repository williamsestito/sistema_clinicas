<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Appointment::with(['client', 'professional.user', 'service'])
            ->where('tenant_id', $user->tenant_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->professional_id, fn($q) => $q->where('professional_id', $request->professional_id))
            ->when($request->date, fn($q) => $q->whereDate('start_at', $request->date))
            ->orderBy('start_at', 'desc');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'professional_id' => 'required|exists:professionals,id',
            'service_id' => 'required|exists:services,id',
            'start_at' => 'required|date_format:Y-m-d H:i',
            'end_at' => 'required|date_format:Y-m-d H:i|after:start_at',
            'source' => 'in:web,staff,whatsapp',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Client::find($request->client_id);
        $professional = Professional::find($request->professional_id);
        $service = Service::find($request->service_id);

        if ($client->tenant_id !== $authUser->tenant_id ||
            $professional->tenant_id !== $authUser->tenant_id ||
            $service->tenant_id !== $authUser->tenant_id) {
            return response()->json(['message' => 'IDs pertencem a outro tenant.'], 403);
        }

        $conflict = Appointment::where('professional_id', $request->professional_id)
            ->whereBetween('start_at', [$request->start_at, $request->end_at])
            ->orWhereBetween('end_at', [$request->start_at, $request->end_at])
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Conflito de horário detectado.'], 409);
        }

        DB::beginTransaction();

        try {
            $appointment = Appointment::create([
                'tenant_id' => $authUser->tenant_id,
                'client_id' => $client->id,
                'professional_id' => $professional->id,
                'service_id' => $service->id,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'status' => 'pending',
                'source' => $request->source ?? 'web',
                'notes' => $request->notes,
            ]);

            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'changed_by_user_id' => $authUser->id ?? null,
                'from_status' => null,
                'to_status' => 'pending',
                'note' => 'Agendamento criado.'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Agendamento criado com sucesso.',
                'data' => $appointment->load(['client', 'professional.user', 'service'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar agendamento.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $user = Auth::user();

        $appointment = Appointment::with(['client', 'professional.user', 'service', 'logs'])
            ->where('tenant_id', $user->tenant_id)
            ->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

        return response()->json($appointment);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        $appointment = Appointment::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'in:pending,confirmed,done,cancelled,no_show',
            'notes' => 'nullable|string|max:500',
            'start_at' => 'sometimes|date_format:Y-m-d H:i',
            'end_at' => 'sometimes|date_format:Y-m-d H:i|after:start_at',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $oldStatus = $appointment->status;
            $appointment->update($request->only(['status', 'notes', 'start_at', 'end_at']));

            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'changed_by_user_id' => $authUser->id,
                'from_status' => $oldStatus,
                'to_status' => $appointment->status,
                'note' => 'Status alterado para ' . $appointment->status
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Agendamento atualizado com sucesso.',
                'data' => $appointment->load(['client', 'professional.user', 'service'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar agendamento.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $authUser = Auth::user();
        $appointment = Appointment::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

        DB::transaction(function () use ($appointment, $authUser) {
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'changed_by_user_id' => $authUser->id,
                'from_status' => $appointment->status,
                'to_status' => 'cancelled',
                'note' => 'Agendamento excluído.'
            ]);
            $appointment->delete();
        });

        return response()->json(['message' => 'Agendamento excluído com sucesso.']);
    }

    public function availableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:professionals,id',
            'date' => 'required|date_format:Y-m-d',
            'service_id' => 'required|exists:services,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json([
            'message' => 'Horários disponíveis para ' . $request->date,
            'slots' => [
                '09:00', '09:30', '10:00', '10:30',
                '11:00', '14:00', '14:30', '15:00'
            ]
        ]);
    }
}
