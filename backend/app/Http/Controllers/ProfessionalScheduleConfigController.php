<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfessionalSchedule;

class ProfessionalScheduleConfigController extends Controller
{
    /**
     * Exibe a tela de configuração da agenda do profissional.
     */
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $diasSemana = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        $schedules = ProfessionalSchedule::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->get();

        return view('professional.schedule_config', compact('diasSemana', 'schedules'));
    }

    /**
     * Atualiza as configurações de agenda do profissional.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $dados = $request->input('schedules', []);

        \Log::info('Schedules recebidos:', $dados);

        foreach ($dados as $weekday => $config) {
            // Ignora linhas vazias
            if (
                empty($config['active']) &&
                empty($config['start_time']) &&
                empty($config['end_time'])
            ) {
                continue;
            }

            $schedule = ProfessionalSchedule::firstOrNew([
                'professional_id' => $user->id,
                'tenant_id'       => $tenantId,
                'day_of_week'     => $weekday,
            ]);

            // 🔹 Garantir tenant_id mesmo em novas criações
            $schedule->tenant_id    = $tenantId;
            $schedule->available    = isset($config['active']);
            $schedule->start_hour   = $config['start_time'] ?? null;
            $schedule->end_hour     = $config['end_time'] ?? null;
            $schedule->break_start  = $config['break_start'] ?? null;
            $schedule->break_end    = $config['break_end'] ?? null;
            $schedule->duration_min = $config['duration_min'] ?? 30;

            $schedule->save();
        }

        return redirect()
            ->route('professional.schedule.config')
            ->with('success', '✅ Configuração de agenda salva com sucesso!');
    }
}
