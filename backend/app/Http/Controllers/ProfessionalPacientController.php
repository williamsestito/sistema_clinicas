<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\User;

class ProfessionalPacientController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $professional = $user->professional;

        if (!$professional) {
            abort(403, 'Acesso negado: este usuário não é um profissional válido.');
        }

        $pacientIds = Appointment::where('professional_id', $professional->id)
            ->distinct()
            ->pluck('client_id');

        $pacients = User::query()
            ->whereIn('id', $pacientIds)
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name')
            ->get();

        return view('professional.pacients', compact('pacients'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $professional = $user->professional;

        if (!$professional) {
            abort(403, 'Acesso negado: este usuário não é um profissional válido.');
        }

        // Busca o paciente
        $pacient = User::findOrFail($id);

        // Verifica se ele realmente teve atendimento com este profissional
        $appointments = Appointment::with(['service'])
            ->where('professional_id', $professional->id)
            ->where('client_id', $pacient->id)
            ->orderByDesc('start_at')
            ->get();

        if ($appointments->isEmpty()) {
            return redirect()
                ->route('professional.pacients')
                ->with('error', 'Este paciente não possui atendimentos registrados com você.');
        }

        return view('professional.pacient_show', compact('pacient', 'appointments'));
    }
}
