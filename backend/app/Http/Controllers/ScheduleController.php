<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = Schedule::with('professional.user')
            ->where('tenant_id', $authUser->tenant_id)
            ->when($request->professional_id, fn($q) => $q->where('professional_id', $request->professional_id))
            ->orderBy('weekday')
            ->orderBy('start_time');

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:professionals,id',
            'weekday' => 'required|integer|min:0|max:6', // 0=Domingo
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_min' => 'integer|min:10|max:120',
            'buffer_before' => 'integer|min:0|max:60',
            'buffer_after' => 'integer|min:0|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $professional = Professional::where('tenant_id', $authUser->tenant_id)
            ->find($request->professional_id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado ou não pertence ao tenant.'], 404);
        }

        $exists = Schedule::where('tenant_id', $authUser->tenant_id)
            ->where('professional_id', $professional->id)
            ->where('weekday', $request->weekday)
            ->where('start_time', $request->start_time)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Já existe um horário configurado nesse dia e hora.'], 409);
        }

        $schedule = Schedule::create([
            'tenant_id' => $authUser->tenant_id,
            'professional_id' => $professional->id,
            'weekday' => $request->weekday,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'slot_min' => $request->slot_min ?? 30,
            'buffer_before' => $request->buffer_before ?? 0,
            'buffer_after' => $request->buffer_after ?? 0,
        ]);

        return response()->json([
            'message' => 'Horário cadastrado com sucesso.',
            'data' => $schedule->load('professional.user')
        ], 201);
    }

    public function show($id)
    {
        $authUser = Auth::user();

        $schedule = Schedule::with('professional.user')
            ->where('tenant_id', $authUser->tenant_id)
            ->find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Horário não encontrado.'], 404);
        }

        return response()->json($schedule);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $schedule = Schedule::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Horário não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'weekday' => 'nullable|integer|min:0|max:6',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'slot_min' => 'nullable|integer|min:10|max:120',
            'buffer_before' => 'nullable|integer|min:0|max:60',
            'buffer_after' => 'nullable|integer|min:0|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedule->update($request->only([
            'weekday', 'start_time', 'end_time', 'slot_min', 'buffer_before', 'buffer_after'
        ]));

        return response()->json([
            'message' => 'Horário atualizado com sucesso.',
            'data' => $schedule->load('professional.user')
        ]);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $schedule = Schedule::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Horário não encontrado.'], 404);
        }

        $schedule->delete();

        return response()->json(['message' => 'Horário excluído com sucesso.']);
    }
}
