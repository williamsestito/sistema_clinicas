<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Professional;
use App\Models\ProfessionalProcedure;
use App\Models\SchedulePeriod;
use App\Models\SchedulePeriodDay;
use App\Models\BlockedDate;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientScheduleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAGENS – UF, Cidade, Especialidades, Procedimentos, Profissionais
    |--------------------------------------------------------------------------
    */

    public function estados()
    {
        return Professional::where('active', true)
            ->whereNotNull('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');
    }

    public function cidades(Request $request)
    {
        $request->validate(['state' => 'required|string|size:2']);

        return Professional::where('active', true)
            ->where('state', $request->state)
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }

    public function especialidades(Request $request)
    {
        $q = Professional::where('active', true);

        if ($request->filled('state')) $q->where('state', $request->state);
        if ($request->filled('city'))  $q->where('city', $request->city);

        $lista = [];

        foreach ($q->get() as $p) {
            $lista = array_merge($lista, (array) $p->specialty);
        }

        return array_values(array_unique($lista));
    }

    public function procedimentos(Request $request)
    {
        $q = ProfessionalProcedure::whereHas('professional', fn($p) =>
            $p->where('active', true)
        );

        if ($request->filled('state')) {
            $q->whereHas('professional', fn($p) =>
                $p->where('state', $request->state)
            );
        }

        if ($request->filled('city')) {
            $q->whereHas('professional', fn($p) =>
                $p->where('city', $request->city)
            );
        }

        if ($request->filled('specialty')) {
            $q->whereHas('professional', fn($p) =>
                $p->whereJsonContains('specialty', $request->specialty)
            );
        }

        return $q->distinct()->orderBy('name')->pluck('name');
    }

    public function profissionais(Request $request)
    {
        $q = Professional::where('active', true);

        if ($request->filled('state')) $q->where('state', $request->state);
        if ($request->filled('city'))  $q->where('city', $request->city);

        if ($request->filled('specialty')) {
            $q->whereJsonContains('specialty', $request->specialty);
        }

        if ($request->filled('procedure')) {
            $q->whereHas('procedures', fn($p) =>
                $p->where('name', $request->procedure)
            );
        }

        return $q->get()->map(fn($p) => [
            'id'              => $p->id,
            'nome'            => $p->display_name,
            'especialidades'  => $p->specialty,
            'cidade'          => $p->city,
            'estado'          => $p->state,
            'endereco'        => $p->full_address,
            'foto'            => $p->photo_url,
            'sobre'           => $p->about,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HORÁRIOS – Busca automática da próxima data válida
    |--------------------------------------------------------------------------
    */

    public function horarios($professionalId, Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $professional = Professional::where('active', true)
            ->findOrFail($professionalId);

        $date = Carbon::parse($request->date);

        if ($date->isPast()) {
            $date = today();
        }

        Log::info('[horarios] início', [
            'requested_date' => $request->date,
            'normalized_date' => $date->toDateString(),
        ]);

        for ($i = 0; $i < 60; $i++) {

            $result = $this->computeSlots($professional, $date);

            if (!empty($result['slots'])) {
                Log::info('[horarios] Data encontrada com horários', [
                    'date' => $date->toDateString(),
                    'slots_count' => count($result['slots']),
                ]);

                return [
                    'success' => true,
                    'date'    => $date->format('Y-m-d'),
                    'slots'   => $result['slots'],
                ];
            }

            $date = $date->copy()->addDay();
        }

        return [
            'success' => false,
            'message' => 'Nenhum horário disponível nos próximos dias.',
            'slots'   => [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Cálculo real dos horários disponíveis
    |--------------------------------------------------------------------------
    */

    private function computeSlots(Professional $professional, Carbon $date)
    {
        $tenantId = $professional->tenant_id;
        $weekday  = $date->dayOfWeek;

        $period = SchedulePeriod::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if (!$period) return ['slots' => []];

        $day = SchedulePeriodDay::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('period_id', $period->id)
            ->where('weekday', $weekday)
            ->first();

        if (!$day || !$day->available) return ['slots' => []];

        if (BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('date', $date)
            ->exists()) return ['slots' => []];

        $slots = $this->generateSlots($day);

        if ($date->isToday()) {
            $now = now()->format('H:i');
            $slots = array_filter($slots, fn($h) => $h > $now);
        }

        // 🔥 CORREÇÃO DEFINITIVA: horários cancelados voltam para a agenda
        $ocupados = Appointment::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('start_at', $date)
            ->whereIn('status', ['pending', 'confirmed'])  // ← ESSENCIAL
            ->pluck('start_at')
            ->map(fn($s) => Carbon::parse($s)->format('H:i'))
            ->toArray();

        // Liberar horários cancelados
        $slots = array_values(array_diff($slots, $ocupados));

        Log::info('[computeSlots] slots finais', [
            'date' => $date->toDateString(),
            'slots' => $slots,
        ]);

        return ['slots' => $slots];
    }

    /*
    |--------------------------------------------------------------------------
    | Geração real dos slots sem loop infinito
    |--------------------------------------------------------------------------
    */
    private function generateSlots(SchedulePeriodDay $day)
    {
        $slots = [];

        if (!$day->start_time || !$day->end_time || !$day->duration) {
            return $slots;
        }

        $start = Carbon::parse($day->start_time)->setDate(2000, 1, 1);
        $end   = Carbon::parse($day->end_time)->setDate(2000, 1, 1);

        $breakStart = $day->break_start ? Carbon::parse($day->break_start)->setDate(2000, 1, 1) : null;
        $breakEnd   = $day->break_end ? Carbon::parse($day->break_end)->setDate(2000, 1, 1) : null;

        while ($start < $end) {

            // intervalo de almoço
            if ($breakStart && $breakEnd && $start >= $breakStart && $start < $breakEnd) {
                $start = $breakEnd->copy();
                continue;
            }

            $slotEnd = $start->copy()->addMinutes($day->duration);

            if ($slotEnd <= $end) {
                $slots[] = $start->format('H:i');
            }

            $start = $slotEnd;
        }

        return $slots;
    }
}
