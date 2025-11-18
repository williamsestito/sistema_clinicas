<?php

namespace App\Http\Controllers;

use App\Models\SchedulePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchedulePeriodController extends Controller
{
    /**
     * Lista períodos (não utilizado diretamente na tela A2)
     */
    public function index()
    {
        $user = Auth::user();

        $periods = SchedulePeriod::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('professional.period_index', compact('periods'));
    }

    /**
     * Criar período
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'active_days' => 'required|array|min:1',
            'active_days.*' => 'integer|min:0|max:6'
        ]);

        $period = SchedulePeriod::create([
            'tenant_id'       => $user->tenant_id,
            'professional_id' => $user->professional->id,
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'active_days'     => $validated['active_days']
        ]);

        // Redireciona para a tela principal com o período recém-criado selecionado
        return redirect()
            ->route('professional.schedule.config', ['period_id' => $period->id])
            ->with('success', 'Período criado com sucesso!');
    }

    /**
     * Exibir período (não usado na UI atual)
     */
    public function show($id)
    {
        $user = Auth::user();

        $period = SchedulePeriod::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->findOrFail($id);

        return view('professional.period_show', compact('period'));
    }

    /**
     * Atualizar período
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $period = SchedulePeriod::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'active_days' => 'required|array|min:1',
            'active_days.*' => 'integer|min:0|max:6'
        ]);

        $period->update($validated);

        return redirect()
            ->route('professional.schedule.config', ['period_id' => $period->id])
            ->with('success', 'Período atualizado com sucesso!');
    }

    /**
     * Apagar período
     */
    public function destroy($id)
    {
        $period = SchedulePeriod::findOrFail($id);
        $period->delete();

        return response()->json([
            'success' => true,
            'message' => 'Período removido com sucesso!'
        ]);
    }

}
