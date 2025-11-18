<?php

namespace App\Http\Controllers;

use App\Models\SchedulePeriod;
use App\Models\SchedulePeriodDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchedulePeriodDayController extends Controller
{
    public function index($periodId)
    {
        $period = SchedulePeriod::where('tenant_id', Auth::user()->tenant_id)
            ->where('professional_id', Auth::user()->professional->id)
            ->findOrFail($periodId);

        return response()->json([
            'success' => true,
            'data' => $period->days()->orderBy('weekday')->get()
        ]);
    }

    public function store(Request $request, $periodId)
    {
        $period = SchedulePeriod::where('tenant_id', Auth::user()->tenant_id)
            ->where('professional_id', Auth::user()->professional->id)
            ->findOrFail($periodId);

        $validated = $request->validate([
            'weekday'      => 'required|integer|min:0|max:6',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'break_start'  => 'nullable|date_format:H:i',
            'break_end'    => 'nullable|date_format:H:i|after:break_start',
            'duration'     => 'required|integer|min:10|max:240',
            'buffer_before'=> 'nullable|integer|min:0|max:60',
            'buffer_after' => 'nullable|integer|min:0|max:60',
            'available'    => 'nullable|boolean'
        ]);

        $exists = SchedulePeriodDay::where('schedule_period_id', $period->id)
            ->where('weekday', $validated['weekday'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe configuração para este dia.'
            ], 409);
        }

        $validated['schedule_period_id'] = $period->id;

        $day = SchedulePeriodDay::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dia configurado com sucesso.',
            'data' => $day
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $day = SchedulePeriodDay::findOrFail($id);

        $this->authorizeDay($day);

        $validated = $request->validate([
            'weekday'      => 'required|integer|min:0|max:6',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'break_start'  => 'nullable|date_format:H:i',
            'break_end'    => 'nullable|date_format:H:i|after:break_start',
            'duration'     => 'required|integer|min:10|max:240',
            'buffer_before'=> 'nullable|integer|min:0|max:60',
            'buffer_after' => 'nullable|integer|min:0|max:60',
            'available'    => 'nullable|boolean'
        ]);

        $exists = SchedulePeriodDay::where('schedule_period_id', $day->schedule_period_id)
            ->where('weekday', $validated['weekday'])
            ->where('id', '!=', $day->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Outro dia já está configurado para este weekday.'
            ], 409);
        }

        $day->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dia atualizado com sucesso.',
            'data' => $day
        ]);
    }

    public function destroy($id)
    {
        $day = SchedulePeriodDay::findOrFail($id);

        $this->authorizeDay($day);

        $day->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dia removido com sucesso.'
        ]);
    }

    private function authorizeDay(SchedulePeriodDay $day)
    {
        $period = $day->period;

        if ($period->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($period->professional_id !== Auth::user()->professional->id) {
            abort(403);
        }
    }
}
