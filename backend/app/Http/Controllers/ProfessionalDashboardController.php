<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\BlockedDate;
use Carbon\CarbonImmutable;
use Carbon\Carbon;

class ProfessionalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $today = CarbonImmutable::today();

        /*
        |--------------------------------------------------------------------------
        | ESTATÍSTICAS
        |--------------------------------------------------------------------------
        */

        // Pendentes
        $totalPendentes = Appointment::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        // Confirmados HOJE
        $totalConfirmadosHoje = Appointment::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->whereDate('start_at', $today)
            ->where('status', 'confirmed')
            ->count();

        // Bloqueios por data (coluna correta = date)
        $bloqueiosAtivos = BlockedDate::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->whereDate('date', '>=', $today)
            ->count();

        // Pacientes atendidos neste mês
        $pacientesMes = Appointment::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->whereMonth('start_at', $today->month)
            ->distinct('client_id')
            ->count('client_id');

        /*
        |--------------------------------------------------------------------------
        | PRÓXIMOS 5 ATENDIMENTOS
        |--------------------------------------------------------------------------
        */

        $proximosAtendimentos = Appointment::with('client')
            ->where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->where('start_at', '>=', Carbon::now())
            ->orderBy('start_at', 'asc')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ÚLTIMOS 5 PACIENTES ATENDIDOS
        |--------------------------------------------------------------------------
        */

        $ultimosPacientes = Appointment::with('client')
            ->where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'done')
            ->orderBy('start_at', 'desc')
            ->limit(5)
            ->get();

        return view('professional.dashboard', compact(
            'user',
            'totalPendentes',
            'totalConfirmadosHoje',
            'bloqueiosAtivos',
            'pacientesMes',
            'proximosAtendimentos',
            'ultimosPacientes'
        ));
    }
}
