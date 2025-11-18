<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SchedulePeriod;
use App\Models\BlockedDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfessionalScheduleConfigController extends Controller
{
    /**
     * Tela principal de configuração da agenda
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        $days = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
        ];

        $periods = SchedulePeriod::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->orderBy('start_date', 'desc')
            ->get();

        $selectedPeriod = null;

        if ($request->has('period_id')) {
            $selectedPeriod = SchedulePeriod::where('tenant_id', $tenantId)
                ->where('professional_id', $professionalId)
                ->find($request->period_id);
        }

        if (!$selectedPeriod) {
            $selectedPeriod = $periods->first();
        }

        $weeklySchedules = collect();

        if ($selectedPeriod) {
            $weeklySchedules = Schedule::where('tenant_id', $tenantId)
                ->where('professional_id', $professionalId)
                ->where('period_id', $selectedPeriod->id)
                ->orderBy('weekday')
                ->get()
                ->keyBy('weekday');
        }

        $blocked = BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->orderBy('date', 'desc')
            ->get();

        return view('professional.schedule_config', [
            'days'            => $days,
            'periods'         => $periods,
            'selectedPeriod'  => $selectedPeriod,
            'weeklySchedules' => $weeklySchedules,
            'blocked'         => $blocked,
        ]);
    }


    /**
     * Criar período
     */
    public function storePeriod(Request $request)
    {
        $user = Auth::user();
        $professionalId = $user->professional->id;

        $validated = $request->validate([
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'active_days'  => 'required|array|min:1',
            'active_days.*'=> 'integer|min:0|max:6',
        ]);

        SchedulePeriod::create([
            'tenant_id'       => $user->tenant_id,
            'professional_id' => $professionalId,
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'active_days'     => $validated['active_days'],
        ]);

        return back()->with('success', 'Período criado com sucesso!');
    }


    /**
     * Excluir período
     */
    public function destroyPeriod($id)
    {
        $user = Auth::user();

        $period = SchedulePeriod::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->findOrFail($id);

        $period->delete();

        return back()->with('success', 'Período removido com sucesso!');
    }



    /**
     * Criar ou atualizar horários semanais
     */
    public function storeWeekly(Request $request)
    {
        $user = Auth::user();

        $selectedPeriod = SchedulePeriod::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->where('id', $request->period_id)
            ->first();

        if (!$selectedPeriod) {
            return back()->withErrors('Nenhum período válido foi selecionado.');
        }

        $activeDays = array_map('intval', $selectedPeriod->active_days);
        $schedules = $request->get('schedules', []);

        foreach ($schedules as $weekday => $data) {

            if (!in_array($weekday, $activeDays)) {
                continue;
            }

            Schedule::updateOrCreate(
                [
                    'tenant_id'       => $user->tenant_id,
                    'professional_id' => $user->professional->id,
                    'period_id'       => $selectedPeriod->id,
                    'weekday'         => $weekday
                ],
                [
                    'start_time'    => $data['start_time'] ?? null,
                    'end_time'      => $data['end_time'] ?? null,
                    'break_start'   => $data['break_start'] ?? null,
                    'break_end'     => $data['break_end'] ?? null,
                    'slot_min'      => $data['slot_min'] ?? 30,
                ]
            );
        }

        return back()->with('success', 'Horários salvos com sucesso!');
    }



    /**
     * Criar bloqueio de data — AGORA AJAX
     */
    public function storeBlock(Request $request)
        {
            $user = Auth::user();
            $professionalId = $user->professional->id;

            $validated = $request->validate([
                'date'   => 'required|date',
                'reason' => 'nullable|string|max:255',
            ]);

            $exists = BlockedDate::where('tenant_id', $user->tenant_id)
                ->where('professional_id', $professionalId)
                ->where('date', $validated['date'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este dia já está bloqueado.'
                ]);
            }

            $item = BlockedDate::create([
                'tenant_id'       => $user->tenant_id,
                'professional_id' => $professionalId,
                'date'            => $validated['date'],
                'reason'          => $validated['reason'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dia bloqueado com sucesso!',
                'item'    => $item
            ]);
        }



    public function destroyBlock($id)
    {
        $user = Auth::user();

        $blocked = BlockedDate::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->findOrFail($id);

        $blocked->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bloqueio removido com sucesso!',
            'id'      => $id,
        ]);
    }


}
