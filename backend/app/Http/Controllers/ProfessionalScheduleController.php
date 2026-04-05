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

        $period = SchedulePeriod::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$period) {
            $period = SchedulePeriod::where('tenant_id', $tenantId)
                ->where('professional_id', $professional->id)
                ->where('start_date', '>', $date)
                ->orderBy('start_date', 'asc')
                ->first();

            if (!$period) {
                return view('professional.schedule', [
                    'date' => $date,
                    'activePeriod' => null,
                    'error' => 'Nenhum período ativo ou futuro encontrado.',
                    'slots' => collect(),
                    'appointments' => collect(),
                    'blocked' => collect(),
                ]);
            }

            $date = $period->start_date->copy();
            $weekday = $date->dayOfWeek;
        }

        $scheduleDay = SchedulePeriodDay::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('period_id', $period->id)
            ->where('weekday', $weekday)
            ->first();

        if (!$scheduleDay) {
            return view('professional.schedule', [
                'date' => $date,
                'activePeriod' => $period,
                'error' => 'Nenhum horário configurado para este dia.',
                'slots' => collect(),
                'appointments' => collect(),
                'blocked' => collect(),
            ]);
        }

        $blocked = BlockedDate::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->where('date', $date->toDateString())
            ->get();

        $slots = $this->generateSlots($scheduleDay);

        // 🔥 Ajuste ESSENCIAL
        $appointments = Appointment::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('start_at', $date)
            ->whereIn('status', ['pending', 'confirmed']) // FIX
            ->orderBy('start_at')
            ->get();

        $slots = collect($slots)->map(function ($slot) use ($appointments, $blocked) {

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
            'date' => $date,
            'activePeriod' => $period,
            'slots' => $slots,
            'appointments' => $appointments,
            'blocked' => $blocked,
        ]);
    }

    private function generateSlots(SchedulePeriodDay $day)
    {
        $slots = collect();

        if (
            empty($day->start_time) ||
            empty($day->end_time) ||
            empty($day->duration) ||
            $day->duration < 1
        ) {
            return $slots;
        }

        try {
            $start = Carbon::parse($day->start_time);
            $end   = Carbon::parse($day->end_time);
        } catch (\Exception $e) {
            return $slots;
        }

        if ($end <= $start) {
            return $slots;
        }

        $breakStart = $day->break_start ? Carbon::parse($day->break_start) : null;
        $breakEnd   = $day->break_end ? Carbon::parse($day->break_end) : null;

        $maxIterations = 500;
        $i = 0;

        while ($start < $end) {

            $i++;
            if ($i > $maxIterations) break;

            $slotEnd = (clone $start)->addMinutes($day->duration);

            if ($slotEnd > $end) break;

            if ($breakStart && $breakEnd) {
                if ($start->between($breakStart, $breakEnd) ||
                    $slotEnd->between($breakStart, $breakEnd)) {

                    if ($breakEnd <= $start) break;

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
