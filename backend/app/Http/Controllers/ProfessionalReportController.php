<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfessionalReportController extends Controller
{
    /**
     * Relatório de atendimentos do profissional
     */
    public function appointments()
    {
        // Placeholder até implementação real
        return view('professional.reports.appointments');
    }

    /**
     * Relatório financeiro do profissional
     */
    public function finance()
    {
        // Placeholder até implementação real
        return view('professional.reports.finance');
    }
}
