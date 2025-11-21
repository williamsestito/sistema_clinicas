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
        $request->validate([
            'state' => 'required|string|size:2'
        ]);

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

        return $q->distinct()
            ->orderBy('name')
            ->pluck('name');
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

        // datas passadas => força hoje
        if ($date->isBefore(today())) {
            $date = today();
        }

        Log::info('[horarios] início', [
            'professional_id' => $professional->id,
            'tenant_id'       => $professional->tenant_id,
            'requested_date'  => $request->date,
            'normalized_date' => $date->toDateString(),
        ]);

        // procura próximos dias por até 60 dias
        for ($i = 0; $i < 60; $i++) {

            $result = $this->computeSlots($professional, $date);

            if (!empty($result['slots'])) {
                Log::info('[horarios] data encontrada com slots', [
                    'professional_id' => $professional->id,
                    'date'            => $date->toDateString(),
                    'slots_count'     => count($result['slots']),
                ]);

                return [
                    'success' => true,
                    'date'    => $date->format('Y-m-d'),
                    'slots'   => $result['slots'],
                ];
            }

            $date = $date->copy()->addDay();
        }

        Log::warning('[horarios] nenhum horário encontrado nos próximos 60 dias', [
            'professional_id' => $professional->id,
        ]);

        return [
            'success' => false,
            'message' => 'Nenhum horário disponível nos próximos dias.',
            'slots'   => [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica principal de cálculo de horários
    |--------------------------------------------------------------------------
    */

    private function computeSlots(Professional $professional, Carbon $date)
    {
        $tenantId = $professional->tenant_id;
        $weekday  = $date->dayOfWeek; // 0=domingo ... 6=sábado

        $period = SchedulePeriod::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if (!$period) {
            Log::info('[computeSlots] sem período válido', [
                'professional_id' => $professional->id,
                'date'            => $date->toDateString(),
            ]);
            return ['slots' => []];
        }

        $day = SchedulePeriodDay::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('period_id', $period->id)
            ->where('weekday', $weekday)
            ->first();

        if (!$day || !$day->available) {
            Log::info('[computeSlots] dia indisponível', [
                'professional_id' => $professional->id,
                'date'            => $date->toDateString(),
                'weekday'         => $weekday,
            ]);
            return ['slots' => []];
        }

        if (BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('date', $date)
            ->exists()) 
        {
            Log::info('[computeSlots] data bloqueada', [
                'professional_id' => $professional->id,
                'date'            => $date->toDateString(),
            ]);
            return ['slots' => []];
        }

        $slots = $this->generateSlots($day);

        // remove passados (apenas se for hoje)
        if ($date->isToday()) {
            $now = now()->format('H:i');
            $slots = array_filter($slots, fn($h) => $h > $now);
        }

        // remove horários já ocupados
        $ocupados = Appointment::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('start_at', $date)
            ->pluck('start_at')
            ->map(fn($s) => Carbon::parse($s)->format('H:i'))
            ->toArray();

        $slots = array_values(array_diff($slots, $ocupados));

        Log::info('[computeSlots] slots gerados', [
            'professional_id' => $professional->id,
            'date'            => $date->toDateString(),
            'slots_count'     => count($slots),
        ]);

        return ['slots' => $slots];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 Geração de slots — com guard rail contra loop infinito
    |--------------------------------------------------------------------------
    */
private function generateSlots(SchedulePeriodDay $day)
{
    $slots = [];

    if (!$day->start_time || !$day->end_time || !$day->duration) {
        return $slots;
    }

    // Garantir sempre HH:MM sem data
    $start = Carbon::createFromFormat('H:i:s', $day->start_time)->setDate(2000,1,1);
    $end   = Carbon::createFromFormat('H:i:s', $day->end_time)->setDate(2000,1,1);

    $breakStart = $day->break_start
        ? Carbon::createFromFormat('H:i:s', $day->break_start)->setDate(2000,1,1)
        : null;

    $breakEnd = $day->break_end
        ? Carbon::createFromFormat('H:i:s', $day->break_end)->setDate(2000,1,1)
        : null;

    $safety = 0;
    $limit = 200; // segurança máxima

    while ($start < $end) {

        $safety++;
        if ($safety > $limit) {
            \Log::error('[generateSlots] LOOP INFINITO DETECTADO', [
                'day_id' => $day->id,
                'iterations' => $safety,
                'limit' => $limit,
                'start' => $start,
                'end' => $end,
                'break_start' => $breakStart,
                'break_end' => $breakEnd,
            ]);
            break;
        }

        // intervalo de almoço
        if ($breakStart && $breakEnd && $start >= $breakStart && $start < $breakEnd) {
            $start = $breakEnd->copy(); // pula direto
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
