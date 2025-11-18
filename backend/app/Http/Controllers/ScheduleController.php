<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        $schedules = Schedule::ofTenant($tenantId)
            ->ofProfessional($professionalId)
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        $validated = $request->validate([
            'weekday' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_min' => 'required|integer|min:10|max:240',
            'buffer_before' => 'nullable|integer|min:0|max:60',
            'buffer_after' => 'nullable|integer|min:0|max:60',
        ]);

        $exists = Schedule::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->where('weekday', $validated['weekday'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Esse dia já está configurado.'
            ], 409);
        }

        $validated['tenant_id'] = $tenantId;
        $validated['professional_id'] = $professionalId;

        $schedule = Schedule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Horário criado.',
            'data' => $schedule
        ]);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $user = Auth::user();

        if ($schedule->tenant_id !== $user->tenant_id ||
            $schedule->professional_id !== $user->professional->id) {
            abort(403);
        }

        $validated = $request->validate([
            'weekday' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_min' => 'required|integer|min:10|max:240',
            'buffer_before' => 'nullable|integer|min:0|max:60',
            'buffer_after' => 'nullable|integer|min:0|max:60',
        ]);

        $exists = Schedule::where('tenant_id', $schedule->tenant_id)
            ->where('professional_id', $schedule->professional_id)
            ->where('weekday', $validated['weekday'])
            ->where('id', '!=', $schedule->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um horário configurado para este dia.'
            ], 409);
        }

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Horário atualizado.',
            'data' => $schedule
        ]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $user = Auth::user();

        if ($schedule->tenant_id !== $user->tenant_id ||
            $schedule->professional_id !== $user->professional->id) {
            abort(403);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Horário removido.'
        ]);
    }
}
