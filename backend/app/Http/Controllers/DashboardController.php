<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Cards principais
        $totalClients = Client::where('tenant_id', $tenantId)->count();
        $totalProfessionals = Professional::where('tenant_id', $tenantId)->where('active', true)->count();
        $totalServices = Service::where('tenant_id', $tenantId)->where('active', true)->count();
        $totalEmployees = User::where('tenant_id', $tenantId)->where('active', true)->count();

        // Agendamentos
        $appointmentsToday = Appointment::where('tenant_id', $tenantId)
            ->whereDate('start_at', $today)
            ->count();

        $appointmentsMonth = Appointment::where('tenant_id', $tenantId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->count();

        $pendingAppointments = Appointment::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $confirmedToday = Appointment::where('tenant_id', $tenantId)
            ->whereDate('start_at', $today)
            ->where('status', 'confirmed')
            ->count();

        // Receita mensal (agendamentos concluídos)
        $monthlyRevenue = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startOfMonth, $endOfMonth])
            ->where('appointments.status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        // Últimos agendamentos
        $latestAppointments = Appointment::where('appointments.tenant_id', $tenantId)
            ->with(['client', 'professional.user', 'service'])
            ->orderBy('start_at', 'desc')
            ->limit(10)
            ->get();

        // Agendamentos por status (para gráfico)
        $appointmentsByStatus = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Novos clientes este mês
        $newClientsMonth = Client::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return view('admin.dashboard', compact(
            'totalClients',
            'totalProfessionals',
            'totalServices',
            'totalEmployees',
            'appointmentsToday',
            'appointmentsMonth',
            'pendingAppointments',
            'confirmedToday',
            'monthlyRevenue',
            'latestAppointments',
            'appointmentsByStatus',
            'newClientsMonth'
        ));
    }
}
