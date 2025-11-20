<?php

namespace App\Http\Controllers;

use App\Models\SchedulePeriod;
use App\Models\SchedulePeriodDay;
use App\Models\BlockedDate;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProfessionalScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professional = $user->professional;

        $date = $request->get('date')
            ? Carbon::parse($request->get('date'))
            : Carbon::today();

        $weekday = $date->dayOfWeek;

        // Encontrar período ativo
        $period = SchedulePeriod::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$period) {
            return view('professional.schedule', [
                'date' => $date,
                'activePeriod' => null,
                'error' => 'Nenhum período ativo configurado para esta data.',
                'slots' => collect(),
                'appointments' => collect(),
                'blocked' => collect(),
            ]);
        }

        // Buscar configuração do dia (SchedulePeriodDay)
        $scheduleDay = SchedulePeriodDay::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('weekday', $weekday)
            ->first();

        if (!$scheduleDay) {
            return view('professional.schedule', [
                'date' => $date,
                'activePeriod' => $period,
                'error' => 'Nenhum horário configurado para este dia da semana.',
                'slots' => collect(),
                'appointments' => collect(),
                'blocked' => collect(),
            ]);
        }

        // Dias bloqueados
        $blocked = BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('date', $date->toDateString())
            ->get();

        // Gerar slots conforme modelo A2
        $slots = $this->generateSlots($scheduleDay);

        // Consultas do dia
        $appointments = Appointment::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('start_at', $date)
            ->orderBy('start_at')
            ->get();

        // Classificação dos slots
        $slots = $slots->map(function ($slot) use ($appointments, $blocked) {

            if ($blocked->count() > 0) {
                return [
                    'start' => $slot['start'],
                    'end'   => $slot['end'],
                    'type'  => 'blocked'
                ];
            }

            foreach ($appointments as $appt) {
                if (
                    $appt->start_at->format('H:i') <= $slot['start'] &&
                    $appt->end_at->format('H:i') > $slot['start']
                ) {
                    return [
                        'start' => $slot['start'],
                        'end'   => $slot['end'],
                        'type'  => 'occupied'
                    ];
                }
            }

            return [
                'start' => $slot['start'],
                'end'   => $slot['end'],
                'type'  => 'available'
            ];
        });

        return view('professional.schedule', [
            'date'         => $date,
            'activePeriod' => $period,
            'slots'        => $slots,
            'appointments' => $appointments,
            'blocked'      => $blocked,
        ]);
    }


    /**
     * Gera slots com base no novo modelo A2 (SchedulePeriodDay)
     */
    private function generateSlots(SchedulePeriodDay $day)
    {
        $slots = collect();

        if (!$day->start_time || !$day->end_time || !$day->duration) {
            return $slots;
        }

        $start = Carbon::parse($day->start_time);
        $end = Carbon::parse($day->end_time);

        $breakStart = $day->break_start ? Carbon::parse($day->break_start) : null;
        $breakEnd   = $day->break_end ? Carbon::parse($day->break_end) : null;

        $maxIterations = 300;
        $i = 0;

        while ($start < $end) {

            $i++;
            if ($i > $maxIterations) break;

            $slotEnd = (clone $start)->addMinutes($day->duration);

            if ($slotEnd > $end) break;

            if ($breakStart && $breakEnd) {
                if ($start->between($breakStart, $breakEnd) ||
                    $slotEnd->between($breakStart, $breakEnd)) {
                    $start = $breakEnd;
                    continue;
                }
            }

            $slots->push([
                'start' => $start->format('H:i'),
                'end'   => $slotEnd->format('H:i'),
            ]);

            $start = $slotEnd;
        }

        return $slots;
    }
}
