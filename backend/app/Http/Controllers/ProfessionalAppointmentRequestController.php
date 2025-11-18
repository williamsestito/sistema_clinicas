<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class ProfessionalAppointmentRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $professional = $user->professional;

        // Pega apenas agendamentos pendentes
        $requests = Appointment::with(['client', 'service'])
            ->where('professional_id', $professional->id)
            ->where('status', 'pending')
            ->orderBy('start_at', 'asc')
            ->get();

        return view('professional.appointments.requests', compact('requests'));
    }

    public function approve($id)
    {
        $appt = Appointment::findOrFail($id);

        $appt->update([
            'status' => 'confirmed'
        ]);

        return back()->with('success', 'Agendamento aprovado com sucesso!');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $appt = Appointment::findOrFail($id);

        $appt->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->reason
        ]);

        return back()->with('success', 'Agendamento rejeitado.');
    }
}
