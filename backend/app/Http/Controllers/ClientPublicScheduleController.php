<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\ProfessionalProcedure;
use App\Models\SchedulePeriod;
use App\Models\SchedulePeriodDay;
use App\Models\BlockedDate;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ClientPublicScheduleController extends Controller
{
    /**
     * Página principal
     */
    public function index()
    {
        return view('client.agendar');
    }

    /**
     * Lista estados com profissionais
     */
    public function estados()
    {
        return Professional::whereNotNull('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');
    }

    /**
     * Lista cidades de um estado
     */
    public function cidades(Request $request)
    {
        return Professional::where('state', $request->state)
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }

    /**
     * Especialidades disponíveis
     */
    public function especialidades(Request $request)
    {
        $query = Professional::query();

        if ($request->state) {
            $query->where('state', $request->state);
        }
        if ($request->city) {
            $query->where('city', $request->city);
        }

        $lista = [];

        foreach ($query->get() as $p) {
            if (is_array($p->specialty)) {
                $lista = array_merge($lista, $p->specialty);
            }
        }

        return array_values(array_unique($lista));
    }

    /**
     * Procedimentos disponíveis
     */
    public function procedimentos(Request $request)
    {
        $query = ProfessionalProcedure::query();

        if ($request->specialty) {
            $query->whereHas('professional', function ($q) use ($request) {
                $q->whereJsonContains('specialty', $request->specialty);
            });
        }

        return $query->distinct()
            ->orderBy('name')
            ->pluck('name');
    }

    /**
     * Retorna lista de profissionais filtrados
     */
    public function profissionais(Request $request)
    {
        $query = Professional::with('procedures', 'user')
            ->where('active', true);

        if ($request->state) {
            $query->where('state', $request->state);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->specialty) {
            $query->whereJsonContains('specialty', $request->specialty);
        }

        if ($request->procedure) {
            $query->whereHas('procedures', function ($q) use ($request) {
                $q->where('name', $request->procedure);
            });
        }

        return $query->get();
    }

    /**
     * Retorna horários disponíveis de um profissional para um dia
     */
    public function horarios($id, Request $request)
    {
        $date = Carbon::parse($request->date);
        $weekday = $date->dayOfWeekIso;

        // Período ativo
        $period = SchedulePeriod::where('professional_id', $id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if (!$period) return [];

        // Configuração do dia
        $day = SchedulePeriodDay::where('professional_id', $id)
            ->where('weekday', $weekday)
            ->first();

        if (!$day || !$day->available) return [];

        // Bloqueio manual
        if (BlockedDate::where('professional_id', $id)->whereDate('date', $date)->exists()) {
            return [];
        }

        // Slots
        $slots = $this->gerarSlots($day->start_time, $day->end_time, $day->duration, $day->break_start, $day->break_end);

        // Consultas já agendadas
        $ocupados = Appointment::where('professional_id', $id)
            ->whereDate('start_at', $date)
            ->pluck('start_at')
            ->map(fn ($s) => Carbon::parse($s)->format('H:i'));

        // Filtrar slots disponíveis
        return array_values(array_filter($slots, fn ($s) => !$ocupados->contains($s)));
    }

    /**
     * Geração de slots para o dia
     */
    private function gerarSlots($start, $end, $duration, $breakStart, $breakEnd)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $breakStart = $breakStart ? Carbon::parse($breakStart) : null;
        $breakEnd   = $breakEnd   ? Carbon::parse($breakEnd)   : null;

        $slots = [];

        while ($start->lt($end)) {

            if ($breakStart && $start->between($breakStart, $breakEnd)) {
                $start->addMinutes($duration);
                continue;
            }

            $slots[] = $start->format('H:i');
            $start->addMinutes($duration);
        }

        return $slots;
    }

    /**
     * Criar pré-agendamento
     */
    public function preAgendar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:professionals,id',
            'procedure'       => 'required|string',
            'date'            => 'required|date',
            'time'            => 'required|string',
            'client_name'     => 'required|string|max:120',
            'client_email'    => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $professional = Professional::with('user')->find($request->professional_id);

        $start = Carbon::parse("{$request->date} {$request->time}");
        $end = $start->copy()->addMinutes(30);

        $appointment = Appointment::create([
            'tenant_id'       => $professional->tenant_id,
            'professional_id' => $professional->id,
            'client_id'       => null, // pré-agendamento anônimo
            'service_id'      => null,
            'start_at'        => $start,
            'end_at'          => $end,
            'status'          => 'pending',
            'source'          => 'client_public',
            'notes'           => "Procedimento: {$request->procedure} | Cliente: {$request->client_name}",
        ]);

        // E-mails
        Mail::raw("Novo pré-agendamento para {$start->format('d/m/Y H:i')}", function ($msg) use ($professional) {
            $msg->to($professional->user->email)->subject('Novo pré-agendamento');
        });

        Mail::raw("Sua solicitação está pendente.", function ($msg) use ($request) {
            $msg->to($request->client_email)->subject('Pré-agendamento recebido');
        });

        return ['success' => true, 'message' => 'Pré-agendamento enviado com sucesso.'];
    }
}
