<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\BlockedDate;
use Carbon\Carbon;

class ProfessionalScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Data selecionada (default = hoje)
        $date = $request->input('date') 
            ? Carbon::parse($request->input('date')) 
            : Carbon::today();

        // Agendamentos do dia
        $appointments = Appointment::with(['client', 'service'])
            ->where('professional_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->whereDate('start_at', $date)
            ->orderBy('start_at')
            ->get();

        // Bloqueios do dia
        $blocked = BlockedDate::where('professional_id', $user->id)
            ->whereDate('date', $date)
            ->get();

        return view('professional.schedule', compact('appointments', 'blocked', 'date'));
    }
}
