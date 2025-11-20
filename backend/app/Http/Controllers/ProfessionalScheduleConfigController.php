<?php

namespace App\Http\Controllers;

use App\Models\SchedulePeriod;
use App\Models\SchedulePeriodDay;
use App\Models\BlockedDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfessionalScheduleConfigController extends Controller
{
    /**
     * Tela principal de configuração da agenda (modelo A2)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        // ---------------------------------------------------------------------
        // PERÍODOS DO PROFISSIONAL
        // ---------------------------------------------------------------------
        $periods = SchedulePeriod::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->orderBy('start_date', 'desc')
            ->get();

        // ---------------------------------------------------------------------
        // PERÍODO SELECIONADO
        // ---------------------------------------------------------------------
        $selectedPeriod = null;
        $periodId = $request->get('period_id');

        if ($periodId) {
            $selectedPeriod = $periods->where('id', $periodId)->first();
        }

        if (!$selectedPeriod && $periods->count() > 0) {
            $selectedPeriod = $periods->first();
        }

        // ---------------------------------------------------------------------
        // DIAS JÁ CONFIGURADOS
        // ---------------------------------------------------------------------
        $weeklySchedules = collect();

        if ($selectedPeriod) {
            $weeklySchedules = SchedulePeriodDay::where('tenant_id', $tenantId)
                ->where('professional_id', $professionalId)
                ->where('period_id', $selectedPeriod->id)
                ->orderBy('weekday')
                ->get()
                ->keyBy('weekday');
        }

        // ---------------------------------------------------------------------
        // DIAS BLOQUEADOS
        // ---------------------------------------------------------------------
        $blocked = BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->orderBy('date', 'desc')
            ->get();

        // ---------------------------------------------------------------------
        // DIAS DA SEMANA
        // ---------------------------------------------------------------------
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return view('professional.schedule-config', [
            'periods'         => $periods,
            'selectedPeriod'  => $selectedPeriod,
            'weeklySchedules' => $weeklySchedules,
            'blocked'         => $blocked,
            'days'            => $days,
        ]);
    }


    /**
     * Carrega a listagem parcial dos dias bloqueados (GET)
     */
    public function indexBlocked()
    {
        $user = Auth::user();

        $blocked = BlockedDate::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('professional.partials._blocked_list', [
            'blocked' => $blocked
        ]);
    }


    /**
     * Criar um novo período
     */
    public function storePeriod(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'active_days' => 'required|array|min:1',
        ]);

        SchedulePeriod::create([
            'tenant_id'       => $tenantId,
            'professional_id' => $professionalId,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'active_days'     => $request->active_days,
        ]);

        return back()->with('success', 'Período criado com sucesso.');
    }


    /**
     * SALVAR *TODOS* HORÁRIOS DA SEMANA DE UMA VEZ (modelo A)
     */
    public function storeWeekly(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        $request->validate([
            'period_id' => 'required|exists:schedule_periods,id',
            'schedules' => 'required|array',
        ]);

        $periodId  = $request->period_id;
        $schedules = $request->schedules;

        foreach ($schedules as $weekday => $data) {

            $start  = $data['start_time']  ?? null;
            $end    = $data['end_time']    ?? null;
            $breakS = $data['break_start'] ?? null;
            $breakE = $data['break_end']   ?? null;
            $dur    = intval($data['slot_min'] ?? $data['duration'] ?? 30);

            if (!$start && !$end && !$breakS && !$breakE) {
                continue;
            }

            if (!$start || !$end) {
                continue;
            }

            SchedulePeriodDay::updateOrCreate(
                [
                    'tenant_id'       => $tenantId,
                    'professional_id' => $professionalId,
                    'period_id'       => $periodId,
                    'weekday'         => $weekday,
                ],
                [
                    'start_time'  => $start,
                    'end_time'    => $end,
                    'break_start' => $breakS,
                    'break_end'   => $breakE,
                    'duration'    => $dur,
                    'available'   => true,
                ]
            );
        }

        return back()->with('success', 'Horários semanais atualizados com sucesso!');
    }


    /**
     * 🔒 BLOQUEAR DIA (POST)
     */
    public function blockDate(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        $request->validate([
            'date'   => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        // Evita bloqueios duplicados
        $exists = BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return response()->json([
                "success" => false,
                "message" => "Este dia já está bloqueado."
            ]);
        }

        // Criação
        $item = BlockedDate::create([
            'tenant_id'       => $tenantId,
            'professional_id' => $professionalId,
            'date'            => $request->date,
            'reason'          => $request->reason,
        ]);

        return response()->json([
            "success" => true,
            "message" => "Dia bloqueado com sucesso!",
            "item"    => $item
        ]);
    }


    /**
     * 🔓 DESBLOQUEAR DIA (DELETE)
     */
    public function unblockDate($id)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professionalId = $user->professional->id;

        $blocked = BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professionalId)
            ->where('id', $id)
            ->first();

        if (!$blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Dia não encontrado.'
            ]);
        }

        $blocked->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dia desbloqueado com sucesso!'
        ]);
    }
}
