<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\FinancialEntry;
use App\Models\Professional;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    /**
     * Relatório de Atendimentos
     */
    public function appointments(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfMonth();

        $professionals = Professional::where('tenant_id', $tenantId)->where('active', true)->with('user')->get();

        $query = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startDate, $endDate])
            ->with(['client', 'professional.user', 'service']);

        if ($request->filled('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('start_at', 'desc')->paginate(20)->appends($request->query());

        // Resumo
        $summary = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startDate, $endDate])
            ->when($request->filled('professional_id'), fn($q) => $q->where('professional_id', $request->professional_id))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed
            ")
            ->first();

        // Atendimentos por profissional
        $byProfessional = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startDate, $endDate])
            ->join('professionals', 'appointments.professional_id', '=', 'professionals.id')
            ->join('users', 'professionals.user_id', '=', 'users.id')
            ->selectRaw('users.name as prof_name, COUNT(*) as total')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        // Atendimentos por dia (para gráfico)
        $byDay = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startDate, $endDate])
            ->when($request->filled('professional_id'), fn($q) => $q->where('professional_id', $request->professional_id))
            ->selectRaw("DATE(start_at) as date, COUNT(*) as total")
            ->groupBy(DB::raw('DATE(start_at)'))
            ->orderBy('date')
            ->get();

        return view('admin.reports.appointments', compact(
            'appointments', 'professionals', 'summary', 'byProfessional', 'byDay',
            'startDate', 'endDate'
        ));
    }

    /**
     * Relatório Financeiro (combinando agendamentos + lançamentos avulsos)
     */
    public function financial(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfMonth();

        $professionals = Professional::where('tenant_id', $tenantId)->where('active', true)->with('user')->get();

        // ── Receita dos Agendamentos (charged_amount ?? service.price) ──
        $baseQuery = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startDate, $endDate])
            ->join('services', 'appointments.service_id', '=', 'services.id');

        $filteredQuery = clone $baseQuery;
        if ($request->filled('professional_id')) {
            $filteredQuery->where('appointments.professional_id', $request->professional_id);
        }
        if ($request->filled('payment_status')) {
            $filteredQuery->where('appointments.payment_status', $request->payment_status);
        }

        // Resumo de agendamentos (usa charged_amount quando disponível)
        $aptFinancial = (clone $filteredQuery)
            ->selectRaw("
                SUM(COALESCE(appointments.charged_amount, services.price)) as total_bruto,
                SUM(CASE WHEN appointments.status = 'done' THEN COALESCE(appointments.charged_amount, services.price) ELSE 0 END) as receita_realizada,
                SUM(CASE WHEN appointments.payment_status = 'paid' THEN COALESCE(appointments.charged_amount, services.price) ELSE 0 END) as total_pago,
                SUM(CASE WHEN appointments.payment_status = 'pending' THEN COALESCE(appointments.charged_amount, services.price) ELSE 0 END) as total_pendente,
                COUNT(*) as total_agendamentos,
                SUM(CASE WHEN appointments.status = 'done' THEN 1 ELSE 0 END) as total_realizados
            ")
            ->first();

        // ── Lançamentos avulsos (módulo financeiro) ──
        $entryQuery = FinancialEntry::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        $entriesIncome  = (clone $entryQuery)->where('type', 'income')->sum('amount');
        $entriesExpense = (clone $entryQuery)->where('type', 'expense')->sum('amount');

        // ── Consolidação ──
        $financial = (object) [
            'total_bruto'       => $aptFinancial->total_bruto ?? 0,
            'receita_realizada' => $aptFinancial->receita_realizada ?? 0,
            'total_pago'        => $aptFinancial->total_pago ?? 0,
            'total_pendente'    => $aptFinancial->total_pendente ?? 0,
            'total_agendamentos'=> $aptFinancial->total_agendamentos ?? 0,
            'total_realizados'  => $aptFinancial->total_realizados ?? 0,
            'entries_income'    => $entriesIncome,
            'entries_expense'   => $entriesExpense,
            'receita_total'     => ($aptFinancial->receita_realizada ?? 0) + $entriesIncome,
            'saldo'             => ($aptFinancial->receita_realizada ?? 0) + $entriesIncome - $entriesExpense,
        ];

        // Recebível por profissional
        $byProfessional = (clone $baseQuery)
            ->join('professionals', 'appointments.professional_id', '=', 'professionals.id')
            ->join('users', 'professionals.user_id', '=', 'users.id')
            ->where('appointments.status', 'done')
            ->selectRaw('users.name as prof_name, SUM(COALESCE(appointments.charged_amount, services.price)) as total, COUNT(*) as qty')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        // Receita por serviço
        $byService = (clone $filteredQuery)
            ->where('appointments.status', 'done')
            ->selectRaw('services.name as service_name, SUM(COALESCE(appointments.charged_amount, services.price)) as total, COUNT(*) as qty')
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->get();

        // Receita agendamentos por dia
        $byDay = (clone $filteredQuery)
            ->where('appointments.status', 'done')
            ->selectRaw("DATE(appointments.start_at) as date, SUM(COALESCE(appointments.charged_amount, services.price)) as total")
            ->groupBy(DB::raw('DATE(appointments.start_at)'))
            ->orderBy('date')
            ->get();

        // Lançamentos avulsos por dia
        $entriesByDay = FinancialEntry::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("date, type, SUM(amount) as total")
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        // Lista de atendimentos
        $appointments = Appointment::where('appointments.tenant_id', $tenantId)
            ->whereBetween('appointments.start_at', [$startDate, $endDate])
            ->when($request->filled('professional_id'), fn($q) => $q->where('professional_id', $request->professional_id))
            ->when($request->filled('payment_status'), fn($q) => $q->where('payment_status', $request->payment_status))
            ->with(['client', 'professional.user', 'service'])
            ->orderBy('start_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.reports.financial', compact(
            'financial', 'professionals', 'byProfessional', 'byService', 'byDay',
            'entriesByDay', 'appointments', 'startDate', 'endDate'
        ));
    }
}
