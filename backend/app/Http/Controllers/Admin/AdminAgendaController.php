<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\BlockedDate;
use App\Models\Professional;
use App\Models\ScheduleException;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAgendaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $professionals = Professional::where('tenant_id', $tenantId)
            ->where('active', true)
            ->with('user')
            ->get();

        $clients = User::where('tenant_id', $tenantId)
            ->where('role', 'client')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $services = Service::where('tenant_id', $tenantId)
            ->where('active', true)
            ->get();

        $clientsJson = $clients->map(function ($c) {
            return ['id' => $c->id, 'name' => $c->name];
        })->values();

        $professionalsJson = $professionals->map(function ($p) {
            return ['id' => $p->id, 'name' => $p->user->name];
        })->values();

        $servicesJson = $services->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'professional_id' => $s->professional_id,
                'duration_min' => $s->duration_min,
                'price' => $s->price,
            ];
        })->values();

        return view('admin.agenda', compact('professionals', 'clients', 'services', 'clientsJson', 'professionalsJson', 'servicesJson'));
    }

    public function events(Request $request)
    {
        $user = Auth::user();

        $query = Appointment::with(['client', 'professional.user', 'service'])
            ->where('tenant_id', $user->tenant_id)
            ->where('status', '!=', 'cancelled');

        if ($request->start) {
            $query->where('start_at', '>=', $request->start);
        }
        if ($request->end) {
            $query->where('end_at', '<=', $request->end);
        }
        if ($request->professional_id) {
            $query->where('professional_id', $request->professional_id);
        }

        $statusColors = [
            'pending'   => '#F59E0B',
            'confirmed' => '#3B82F6',
            'done'      => '#10B981',
            'cancelled' => '#EF4444',
            'no_show'   => '#6B7280',
        ];

        $events = $query->get()->map(function ($apt) use ($statusColors) {
            $color = $apt->color ?: ($statusColors[$apt->status] ?? '#6B7280');

            return [
                'id'              => $apt->id,
                'title'           => $apt->client->name ?? 'Paciente',
                'start'           => $apt->start_at->toIso8601String(),
                'end'             => $apt->end_at->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'extendedProps'   => [
                    'client_id'         => $apt->client_id,
                    'client_name'       => $apt->client->name ?? 'Paciente',
                    'professional_id'   => $apt->professional_id,
                    'professional_name' => $apt->professional->user->name ?? 'Profissional',
                    'service_id'        => $apt->service_id,
                    'service_name'      => $apt->service->name ?? 'Servico',
                    'duration_min'      => $apt->service->duration_min ?? 30,
                    'status'            => $apt->status,
                    'payment_status'    => $apt->payment_status ?? 'pending',
                    'charged_amount'    => $apt->charged_amount,
                    'notes'             => $apt->notes,
                    'color'             => $apt->color,
                ],
            ];
        });

        return response()->json($events);
    }

    public function blockedDates(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $blocked = BlockedDate::where('tenant_id', $tenantId)
            ->when($request->professional_id, fn($q, $v) => $q->where('professional_id', $v))
            ->get()
            ->map(fn($b) => [
                'start'           => $b->date->toDateString(),
                'end'             => $b->date->copy()->addDay()->toDateString(),
                'display'         => 'background',
                'backgroundColor' => '#FEE2E2',
                'title'           => 'Bloqueado: ' . ($b->reason ?? 'Sem motivo'),
                'extendedProps'   => ['type' => 'blocked'],
            ]);

        $holidays = ScheduleException::where('tenant_id', $tenantId)
            ->where('type', 'holiday')
            ->when($request->start, fn($q, $v) => $q->where('date', '>=', $v))
            ->when($request->end, fn($q, $v) => $q->where('date', '<=', $v))
            ->get()
            ->map(fn($h) => [
                'start'           => $h->date->toDateString(),
                'end'             => $h->date->copy()->addDay()->toDateString(),
                'display'         => 'background',
                'backgroundColor' => '#DBEAFE',
                'title'           => 'Feriado: ' . ($h->reason ?? ''),
                'extendedProps'   => ['type' => 'holiday'],
            ]);

        return response()->json($blocked->merge($holidays));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'client_id'       => 'required|exists:users,id',
            'professional_id' => 'required|exists:professionals,id',
            'service_id'      => 'required|exists:services,id',
            'start_at'        => 'required|date',
            'end_at'          => 'required|date|after:start_at',
            'payment_status'  => 'in:pending,paid,plan',
            'charged_amount'  => 'nullable|numeric|min:0',
            'color'           => 'nullable|string|max:7',
            'notes'           => 'nullable|string|max:500',
        ]);

        $date = Carbon::parse($request->start_at)->toDateString();
        $professionalId = $request->professional_id;

        $validation = $this->validateDateAndConflict(
            $user->tenant_id, $professionalId, $date,
            $request->start_at, $request->end_at
        );
        if ($validation) {
            return $validation;
        }

        DB::beginTransaction();
        try {
            $appointment = Appointment::create([
                'tenant_id'       => $user->tenant_id,
                'client_id'       => $request->client_id,
                'professional_id' => $professionalId,
                'service_id'      => $request->service_id,
                'start_at'        => $request->start_at,
                'end_at'          => $request->end_at,
                'status'          => 'pending',
                'source'          => 'staff',
                'payment_status'  => $request->payment_status ?? 'pending',
                'charged_amount'  => $request->charged_amount,
                'color'           => $request->color,
                'notes'           => $request->notes,
            ]);

            AppointmentLog::create([
                'appointment_id'     => $appointment->id,
                'changed_by_user_id' => $user->id,
                'from_status'        => null,
                'to_status'          => 'pending',
                'note'               => 'Agendamento criado via painel admin.',
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Agendamento criado com sucesso.',
                'data'    => $appointment->load(['client', 'professional.user', 'service']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar agendamento.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $appointment = Appointment::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $request->validate([
            'start_at'        => 'sometimes|date',
            'end_at'          => 'sometimes|date|after:start_at',
            'status'          => 'sometimes|in:pending,confirmed,done,cancelled,no_show',
            'payment_status'  => 'sometimes|in:pending,paid,plan',
            'charged_amount'  => 'nullable|numeric|min:0',
            'color'           => 'nullable|string|max:7',
            'notes'           => 'nullable|string|max:500',
            'client_id'       => 'sometimes|exists:users,id',
            'professional_id' => 'sometimes|exists:professionals,id',
            'service_id'      => 'sometimes|exists:services,id',
        ]);

        $startAt = $request->start_at ?? $appointment->start_at;
        $endAt = $request->end_at ?? $appointment->end_at;
        $professionalId = $request->professional_id ?? $appointment->professional_id;

        if ($request->has('start_at') || $request->has('end_at')) {
            $date = Carbon::parse($startAt)->toDateString();

            $validation = $this->validateDateAndConflict(
                $user->tenant_id, $professionalId, $date,
                $startAt, $endAt, $appointment->id
            );
            if ($validation) {
                return $validation;
            }
        }

        DB::beginTransaction();
        try {
            $oldStatus = $appointment->status;
            $appointment->update($request->only([
                'start_at', 'end_at', 'status', 'payment_status',
                'charged_amount', 'color', 'notes', 'client_id', 'professional_id', 'service_id',
            ]));

            $noteText = $request->has('start_at')
                ? 'Agendamento remarcado via painel admin.'
                : 'Agendamento atualizado via painel admin.';

            AppointmentLog::create([
                'appointment_id'     => $appointment->id,
                'changed_by_user_id' => $user->id,
                'from_status'        => $oldStatus,
                'to_status'          => $appointment->status,
                'note'               => $noteText,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Agendamento atualizado com sucesso.',
                'data'    => $appointment->load(['client', 'professional.user', 'service']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar agendamento.'], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $appointment = Appointment::where('tenant_id', $user->tenant_id)->findOrFail($id);

        DB::transaction(function () use ($appointment, $user) {
            $oldStatus = $appointment->status;
            $appointment->update(['status' => 'cancelled']);

            AppointmentLog::create([
                'appointment_id'     => $appointment->id,
                'changed_by_user_id' => $user->id,
                'from_status'        => $oldStatus,
                'to_status'          => 'cancelled',
                'note'               => 'Agendamento cancelado via painel admin.',
            ]);
        });

        return response()->json(['message' => 'Agendamento cancelado com sucesso.']);
    }

    private function validateDateAndConflict($tenantId, $professionalId, $date, $startAt, $endAt, $excludeId = null)
    {
        $blocked = BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->where('date', $date)
            ->exists();

        if ($blocked) {
            return response()->json([
                'message' => 'Esta data esta bloqueada para o profissional selecionado.',
            ], 409);
        }

        $holiday = ScheduleException::where('tenant_id', $tenantId)
            ->where('date', $date)
            ->where('type', 'holiday')
            ->exists();

        if ($holiday) {
            return response()->json(['message' => 'Esta data e um feriado.'], 409);
        }

        $conflict = Appointment::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->when($excludeId, function ($q) use ($excludeId) { return $q->where('id', '!=', $excludeId); })
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Conflito de horario com outro agendamento.',
            ], 409);
        }

        return null;
    }
}
