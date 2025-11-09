<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\BlockedDate;
use Carbon\CarbonImmutable;

class ProfessionalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $today = CarbonImmutable::today();

        // === Estatísticas ===
        $totalPendentes = Appointment::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $totalConfirmadosHoje = Appointment::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->whereDate('start_at', $today)
            ->where('status', 'confirmed')
            ->count();

        $bloqueiosAtivos = BlockedDate::where('professional_id', $user->id)
            ->whereDate('date', '>=', $today)
            ->count();

        $pacientesMes = Appointment::where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->whereMonth('start_at', $today->month)
            ->distinct('client_id')
            ->count('client_id');

        // === Próximos atendimentos ===
        $proximosAtendimentos = Appointment::with('client')
            ->where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->where('start_at', '>=', $today->startOfDay())
            ->orderBy('start_at', 'asc')
            ->limit(5)
            ->get();

        // === Últimos pacientes atendidos ===
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
