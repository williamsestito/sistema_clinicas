<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\FinancialEntry;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $totalEmployees = User::where('tenant_id', $tenantId)->where('active', true)->whereIn('role', ['admin', 'professional', 'owner'])->count();

        // Agendamentos
        $appointmentsToday = Appointment::where('tenant_id', $tenantId)
            ->whereDate('start_at', $today)
            ->count();

        $pendingAppointments = Appointment::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $confirmedToday = Appointment::where('tenant_id', $tenantId)
            ->whereDate('start_at', $today)
            ->where('status', 'confirmed')
            ->count();

        // Receita mensal — usa charged_amount quando disponível, senão usa services.price
        $monthlyRevenue = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startOfMonth, $endOfMonth])
            ->where('appointments.status', 'done')
            ->leftJoin('services', 'appointments.service_id', '=', 'services.id')
            ->selectRaw('COALESCE(SUM(COALESCE(appointments.charged_amount, services.price, 0)), 0) as total')
            ->value('total');

        // ── GRÁFICOS: Agendamentos ──

        // Pizza: agendamentos por status (mês atual)
        $appointmentsByStatus = Appointment::where('tenant_id', $tenantId)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Barras: agendamentos por dia (últimos 7 dias)
        $appointmentsByDay = Appointment::where('tenant_id', $tenantId)
            ->whereBetween('start_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
            ->selectRaw('DATE(start_at) as day, count(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // Preenche dias sem agendamentos
        $appointmentsDayLabels = [];
        $appointmentsDayValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->format('Y-m-d');
            $appointmentsDayLabels[] = Carbon::parse($d)->translatedFormat('D d/m');
            $appointmentsDayValues[] = $appointmentsByDay[$d] ?? 0;
        }

        // ── GRÁFICOS: Financeiro ──

        // Pizza: lançamentos por categoria (mês atual)
        $financialByCategory = FinancialEntry::where('financial_entries.tenant_id', $tenantId)
            ->whereBetween('financial_entries.date', [$startOfMonth, $endOfMonth])
            ->join('financial_categories', 'financial_entries.category_id', '=', 'financial_categories.id')
            ->selectRaw('financial_categories.name as cat_name, financial_categories.color as cat_color, SUM(financial_entries.amount) as total')
            ->groupBy('financial_categories.name', 'financial_categories.color')
            ->get();

        // Barras: receitas vs despesas nos últimos 6 meses
        $financialMonthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $label = $mStart->translatedFormat('M/Y');

            $income = FinancialEntry::where('tenant_id', $tenantId)
                ->where('type', 'income')
                ->whereBetween('date', [$mStart, $mEnd])
                ->sum('amount');

            $expense = FinancialEntry::where('tenant_id', $tenantId)
                ->where('type', 'expense')
                ->whereBetween('date', [$mStart, $mEnd])
                ->sum('amount');

            $financialMonthly[] = [
                'label'   => $label,
                'income'  => (float) $income,
                'expense' => (float) $expense,
            ];
        }

        return view('admin.dashboard', compact(
            'totalClients',
            'totalProfessionals',
            'totalServices',
            'totalEmployees',
            'appointmentsToday',
            'pendingAppointments',
            'confirmedToday',
            'monthlyRevenue',
            'appointmentsByStatus',
            'appointmentsDayLabels',
            'appointmentsDayValues',
            'financialByCategory',
            'financialMonthly'
        ));
    }
}
