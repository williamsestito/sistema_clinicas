<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\BlockedDate;
use App\Models\SchedulePeriod;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProfessionalScheduleController extends Controller
{
    /**
     * Exibição diária da agenda do profissional.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $professional = $user->professional;

        // Data atual ou selecionada
        $date = $request->get('date')
            ? Carbon::parse($request->get('date'))
            : Carbon::today();

        $weekday = $date->dayOfWeek;

        // Período ativo
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

        // Horários do dia (modelo A2)
        $scheduleDay = Schedule::where('tenant_id', $tenantId)
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

        // Gerar slots disponíveis
        $slots = collect($scheduleDay->generateSlots());

        // Consultas do dia
        $appointments = Appointment::where('tenant_id', $tenantId)
            ->where('professional_id', $professional->id)
            ->whereDate('start_at', $date)
            ->orderBy('start_at')
            ->get();

        // Marcar estados dos slots
        $slots = $slots->map(function ($slot) use ($appointments, $blocked) {
            $slotStart = $slot['start'];
            $slotEnd   = $slot['end'];

            // Bloqueado
            if ($blocked->count() > 0) {
                return [
                    'start' => $slotStart,
                    'end'   => $slotEnd,
                    'type'  => 'blocked'
                ];
            }

            // Ocupado
            foreach ($appointments as $appt) {
                if (
                    $appt->start_at->format('H:i') <= $slotStart &&
                    $appt->end_at->format('H:i')   > $slotStart
                ) {
                    return [
                        'start' => $slotStart,
                        'end'   => $slotEnd,
                        'type'  => 'occupied'
                    ];
                }
            }

            return [
                'start' => $slotStart,
                'end'   => $slotEnd,
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
}
