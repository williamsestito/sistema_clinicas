<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentLog;
use App\Models\NotificationLog;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLogController extends Controller
{
    /**
     * Logs de alteração de agendamentos
     */
    public function appointments(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30);
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfDay();

        $logs = AppointmentLog::whereHas('appointment', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->whereBetween('changed_at', [$startDate, $endDate])
            ->with(['appointment.client', 'appointment.professional.user', 'appointment.service', 'changedBy'])
            ->orderBy('changed_at', 'desc')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.logs.appointments', compact('logs', 'startDate', 'endDate'));
    }

    /**
     * Logs de notificações enviadas
     */
    public function notifications(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30);
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfDay();

        $query = NotificationLog::where('tenant_id', $tenantId)
            ->whereBetween('sent_at', [$startDate, $endDate])
            ->with(['appointment.client', 'appointment.professional.user']);

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('sent_at', 'desc')
            ->paginate(25)
            ->appends($request->query());

        // Resumo
        $summary = NotificationLog::where('tenant_id', $tenantId)
            ->whereBetween('sent_at', [$startDate, $endDate])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN channel = 'email' THEN 1 ELSE 0 END) as email,
                SUM(CASE WHEN channel = 'whatsapp' THEN 1 ELSE 0 END) as whatsapp
            ")
            ->first();

        return view('admin.logs.notifications', compact('logs', 'summary', 'startDate', 'endDate'));
    }
}
